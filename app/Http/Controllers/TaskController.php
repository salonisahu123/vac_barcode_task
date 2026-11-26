<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskUser;
use App\Models\User;  
use App\Models\Subtask;
use App\Models\Group;
use App\Models\Location;
use App\Models\Employee;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use App\Notifications\TaskUpdatedNotification;
use App\Exports\TasksExport;
use Maatwebsite\Excel\Facades\Excel;

class TaskController extends Controller
{
    /* =======================================
       QUERY BUILDER (Shared by Index, Filter, Export)
    ======================================= */
    private function buildTaskQuery(Request $request)
    {
        $user = Auth::user();
        $employeeId = Employee::where('user_id', $user->id)->value('id');

        $query = Task::with([
            'createdBy',
            'location',
            'group',
            'taskUsers.employee.user',
            'subtask',
            'latestRemark.user'
        ]);

        if (hasPermission('task_view_all')) {
            // Full access to view all tasks
        } elseif (hasPermission('task_view_own')) {
            $query->where(function ($taskQuery) use ($employeeId, $user) {
                $taskQuery->where('user_id', $user->id)
                    ->orWhereHas('taskUsers', function ($q) use ($employeeId) {
                        $q->where('assigned_to', $employeeId);
                    });
            });
        } else {
            $query->whereRaw('1 = 0');
        }

        // Location Filter
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        // Group Filter
        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        // Employee Filter
        if ($request->filled('employee_id')) {
            $query->whereHas('taskUsers', function ($q) use ($request) {
                $q->where('assigned_to', $request->employee_id);
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'all') {
                // Show all statuses - no condition
            } elseif ($status === 'pending_in_progress') {
                $query->whereIn('status', ['pending', 'in_progress']);
            } else {
                $query->where('status', $status);
            }
        } else {
            // Default: Pending & In Progress
            $query->whereIn('status', ['pending', 'in_progress']);
        }

        // Search Filter (Title, Task Type, Subtask, Location, Group, Assigned User, Created By, Dates)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('task_type', 'like', "%{$search}%")
                  ->orWhere('from_date', 'like', "%{$search}%")
                  ->orWhere('to_date', 'like', "%{$search}%")
                  ->orWhereHas('location', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('group', function ($sub) use ($search) {
                      $sub->where('group_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('subtask', function ($sub) use ($search) {
                      $sub->where('subtask', 'like', "%{$search}%");
                  })
                  ->orWhereHas('createdBy', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('taskUsers.employee.user', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  });
            });
        }

        return $query;
    }

    /* =======================================
       LIST (Server-Side Paginated)
    ======================================= */
    public function index(Request $request)
    {
        if (!hasPermission('task_view_all') && !hasPermission('task_view_own')) {
            return response()->view('errors.no-access');
        }

        $user = Auth::user();
        $isAdmin = $user->user_type == 'admin';
        $employeeId = Employee::where('user_id', $user->id)->value('id');

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $query = $this->buildTaskQuery($request);
        $tasks = $query->latest()->paginate($perPage)->withQueryString();

        $locations = Location::all();
        $groups = Group::all();
        $employees = Employee::whereHas('user', function($q) {
            $q->where('is_active', 1);
        })->with('user')->get();
        $subtasks = Subtask::pluck('subtask', 'id');

        return view('tasks.index', compact(
            'tasks',
            'locations',
            'groups',
            'employees',
            'isAdmin',
            'subtasks',
            'employeeId',
            'perPage'
        ));
    }

    /* =======================================
       AJAX FILTER & PAGINATION
    ======================================= */
    public function filter(Request $request)
    {
        if (!hasPermission('task_view_all') && !hasPermission('task_view_own')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $isAdmin = auth()->user()->user_type == 'admin';
        $subtasks = Subtask::pluck('subtask', 'id');

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $query = $this->buildTaskQuery($request);
        $tasks = $query->latest()->paginate($perPage);

        return response()->json([
            'html' => view('tasks.partials.rows', compact('tasks', 'isAdmin', 'subtasks'))->render(),
            'pagination' => view('tasks.partials.pagination', compact('tasks'))->render(),
            'total' => $tasks->total(),
            'from' => $tasks->firstItem() ?? 0,
            'to' => $tasks->lastItem() ?? 0,
            'current_page' => $tasks->currentPage(),
            'last_page' => $tasks->lastPage(),
        ]);
    }

    /* =======================================
       EXPORT (Excel & CSV with all active filters)
    ======================================= */
    public function export(Request $request)
    {
        if (!hasPermission('task_view_all') && !hasPermission('task_view_own')) {
            abort(403, 'Unauthorized');
        }

        $query = $this->buildTaskQuery($request);
        $tasks = $query->latest()->get();

        $format = strtolower($request->get('format', 'xlsx'));
        $fileName = 'tasks_' . date('Y_m_d_His') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');

        if ($format === 'csv') {
            return Excel::download(
                new TasksExport($tasks),
                $fileName,
                \Maatwebsite\Excel\Excel::CSV
            );
        }

        return Excel::download(
            new TasksExport($tasks),
            $fileName
        );
    }

    /* =======================================
       CREATE PAGE
    ======================================= */
    public function create()
    {
        if (!hasPermission('task_add')) {
            return response()->view('errors.no-access');
        }

        $employees = Employee::whereHas('user', function($q) {
            $q->where('is_active', 1);
        })->with('user')->get();
        $locations = Location::all();
        $groups    = Group::all();
        $subtask   = Subtask::all();
    
        return view('tasks.create', compact(
            'employees',
            'locations',
            'groups',
            'subtask'
        ));
    }


    /* =======================================
       STORE
    ======================================= */
    public function store(Request $request)
    {
       
        if (!hasPermission('task_add')) {
            return response()->view('errors.no-access');
        }

        $request->validate([
            'title'         => 'required|string|max:255',
            'task_type'     => 'required|in:individual,location',
            'subtask_id' => 'nullable|exists:subtasks,id',
            'status'        => 'required|in:pending,in_progress,completed',
            'from_date'     => 'required|date',
            'to_date'       => 'required|date|after_or_equal:from_date',
            'description'   => 'nullable|string',
            'file'          => 'nullable|file|max:2048',

            'mode' => 'required_if:task_type,location|nullable|in:location_wise,group_team',

            'assigned_to'   => 'required_if:task_type,individual|required_if:mode,location_wise|array',
            'assigned_to.*' => 'exists:employees,id',

            'location_id'   => 'required_if:mode,location_wise|nullable|exists:locations,id',
            'group_id'      => 'required_if:mode,group_team|nullable|exists:groups,id',

        ],[
            'title.required' => 'Title is required.',
            'task_type.required' => 'Please select task type.',
            'assigned_to.required_if' => 'Please select employee.',
            'mode.required_if' => 'Please select mode.',
            'location_id.required_if' => 'Please select location.',
            'group_id.required_if' => 'Please select group.',
            'status.required' => 'Please select status.',
            'from_date.required' => 'Please select from date.',
            'to_date.required' => 'Please select to date.',
            'to_date.after_or_equal' => 'To date must be after from date.',
        ]);

        /* FILE */
        $fileName = null;

        if ($request->hasFile('file')) {
            $fileName = time().'_'.$request->file('file')->getClientOriginalName();
            $request->file('file')->move(public_path('uploads/tasks'), $fileName);
        }

        /* TASK CREATE */
        $task = Task::create([
            'title'       => $request->title,
            'user_id'     => Auth::id(),
            'task_type'   => $request->task_type,
            'subtask_id'  => $request->subtask_id,
            'mode'        => $request->mode,
            'status'      => $request->status,
            'from_date'   => $request->from_date,
            'to_date'     => $request->to_date,
            'description' => $request->description,
            'file'        => $fileName,
            'location_id' => $request->location_id,
            'group_id'    => $request->group_id,
        ]);

        /* MULTIPLE USERS ATTACH */
        if ($request->task_type == 'individual' || $request->mode == 'location_wise') {

            if ($request->filled('assigned_to')) {

                foreach ($request->assigned_to as $assignedId) {

                    $task->taskUsers()->create([
                        'assigned_to' => $assignedId
                    ]);
                }
            }
        }

        /* GROUP TEAM */
        // if ($request->mode == 'group_team') {

        //     $groupEmployees = \DB::table('group_employees')
        //                     ->where('group_id', $request->group_id)
        //                     ->pluck('employee_id');

        //     foreach ($groupEmployees as $empId) {

        //         $task->taskUsers()->create([
        //             'assigned_to' => $empId
        //         ]);
        //     }
        // }
        
        if ($request->mode == 'group_team') {

        $groupEmployees = \DB::table('group_employees')
            ->join('employees', 'group_employees.employee_id', '=', 'employees.id')
            ->join('users', 'employees.user_id', '=', 'users.id')
            ->where('group_employees.group_id', $request->group_id)
            ->where('users.is_active', 1)
            ->whereDate('group_employees.joined_at', '<=', $request->to_date)
            ->where(function ($query) use ($request) {
                $query->whereNull('group_employees.removed_at')
                    ->orWhereDate('group_employees.removed_at', '>=', $request->to_date);
            })
            ->pluck('group_employees.employee_id')
            ->unique();
    
        foreach ($groupEmployees as $empId) {
    
            $task->taskUsers()->create([
                'assigned_to' => $empId,
            ]);
        }
    }

        /* ACTIVITY */
        Activity::create([
            'user_id'     => Auth::id(),
            'table_name'  => 'tasks',
            'table_id'    => $task->id,
            'operation'   => 'create',
            'description' => 'Task "'.$task->title.'" created',
        ]);

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Task created successfully');
    }

    /* =======================================
       SHOW
    ======================================= */
   public function show($id)
{
    $task = Task::with([
        'createdBy',
        'location',
        'group',
        'taskUsers.employee'
    ])->findOrFail($id);

    $user = Auth::user();
    $employeeId = \App\Models\Employee::where('user_id', $user->id)->value('id');
    
    $isAssigned = $task->taskUsers
        ->where('assigned_to', $employeeId)
        ->count() > 0;
    
    $isCreator = $task->user_id == $user->id;

    $isDashboardAccess = false;
    if ($user->user_type != 'admin') {
        $assignedDashboardIds = \DB::table('employee_dashboards')
            ->where('employee_id', $employeeId)
            ->pluck('dashboard_id')
            ->toArray();
            
        if (!empty($assignedDashboardIds)) {
            $assignedDashboards = \App\Models\CustomDashboard::whereIn('id', $assignedDashboardIds)->get();
            $enabledSubtaskNames = [];
            foreach ($assignedDashboards as $assignedDashboard) {
                $config = json_decode($assignedDashboard->view_json, true) ?? [];
                $taskSubtasks = data_get($config, 'task_module.subtasks', []);
                foreach ($taskSubtasks as $subtaskName => $isEnabled) {
                    if ($isEnabled === true || $isEnabled === 1 || $isEnabled === '1') {
                        $enabledSubtaskNames[] = trim($subtaskName);
                    }
                }
            }
            $enabledSubtaskIds = \App\Models\Subtask::whereIn('subtask', $enabledSubtaskNames)->pluck('id')->toArray();
            if (in_array($task->subtask_id, $enabledSubtaskIds)) {
                $isDashboardAccess = true;
            }
        }
    }

    if (
        !hasPermission('task_view_all') &&
        !(
            hasPermission('task_view_own') &&
            ($isAssigned || $isCreator)
        ) && 
        !$isDashboardAccess
    ) {
        return response()->view('errors.no-access');
    }
    
    

    $assignedEmployees = $task->taskUsers->map(function ($assign) {
        return [
            'id' => $assign->employee->id ?? null,
            'name' => $assign->employee->name ?? '-',
            'image' => $assign->employee->image
                ? asset('uploads/employees/' . $assign->employee->image)
                : asset('images/default-user.png'),
        ];
    });

    $activities = \App\Models\Activity::where('table_name', 'tasks')
        ->where('table_id', $task->id)
        ->whereIn('operation', ['remark', 'subtask_change', 'status_change'])
        ->orderBy('created_at', 'desc')
        ->get();
        
        
    $task->subtask_name = '-';
    
    if (!empty($task->subtask_id)) {
    
        $task->subtask_name = \App\Models\Subtask::where(
            'id',
            $task->subtask_id
        )->value('subtask') ?? '-';
    }
        
        
    return view('tasks.show', [
        'task' => $task,   
        'employees' => $assignedEmployees,
        'activities' => $activities,
        
    ]);
}

    /* =======================================
       EDIT PAGE
    ======================================= */

    public function edit($id)
        {
            $task = Task::with('taskUsers')->findOrFail($id);

            $user = Auth::user();

            $employeeId = Employee::where(
                'user_id',
                $user->id
            )->value('id');

            $isAssigned = $task->taskUsers
                ->where('assigned_to', $employeeId)
                ->count() > 0;

            $isCreator = $task->user_id == $user->id;

            if (
                $user->user_type == 'admin' ||
                hasPermission('task_edit') ||
                hasPermission('task_view_all') ||
                hasPermission('task_view_own')
            ) {

            } else {
                return response()->view('errors.no-access');
            }

        $employees = Employee::whereHas('user', function($q) {
            $q->where('is_active', 1);
        })->with('user')->get();
        $locations = Location::all();
        $groups    = Group::all();
        $subtask = Subtask::all();

        $individualEmployees = $task->task_type == 'individual' ? $task->taskUsers->pluck('assigned_to')->toArray() : [];
        $locationEmployees = $task->task_type == 'location' ? $task->taskUsers->pluck('assigned_to')->toArray() : [];

        return view('tasks.edit', compact(
            'task',
            'employees',
            'locations',
            'groups',
            'subtask',
            'individualEmployees',
            'locationEmployees'
        ));
    }

    /* =======================================
       UPDATE
    ======================================= */

public function update(Request $request, $id)
{
    $task = Task::with('taskUsers')->findOrFail($id);

    $user = Auth::user();

    $employeeId = Employee::where(
        'user_id',
        $user->id
    )->value('id');

    $isAssigned = $task->taskUsers
        ->where('assigned_to', $employeeId)
        ->count() > 0;

    $isCreator = $task->user_id == $user->id;

    if (
        $user->user_type == 'admin' ||
        hasPermission('task_edit') ||
        hasPermission('task_view_all') ||
        hasPermission('task_view_own')
    ) {

    } else {
        return response()->view('errors.no-access');
    }
        $request->validate([
            'title'         => 'required|string|max:255',
            'task_type'     => 'required|in:individual,location',
            'subtask_id'    => 'nullable|exists:subtasks,id',
            'status'        => 'required|in:pending,in_progress,completed',
            'from_date'     => 'required|date',
            'to_date'       => 'required|date|after_or_equal:from_date',
            'description'   => 'nullable|string',
            'file'          => 'nullable|file|max:2048',
            'mode'          => 'required_if:task_type,location|nullable|in:location_wise,group_team',
            'assigned_to'   => 'nullable|array',
            'assigned_to.*' => 'exists:employees,id',
            'location_id'   => 'required_if:mode,location_wise|nullable|exists:locations,id',
            'group_id'      => 'required_if:mode,group_team|nullable|exists:groups,id',
            
        ]);

        /* FILE */
        if ($request->hasFile('file')) {

            if ($task->file && file_exists(public_path('uploads/tasks/'.$task->file))) {
                unlink(public_path('uploads/tasks/'.$task->file));
            }

            $fileName = time().'_'.$request->file('file')->getClientOriginalName();

            $request->file('file')->move(public_path('uploads/tasks'), $fileName);

            $task->file = $fileName;
        }

        /* TASK UPDATE */
        $oldSubtaskId = $task->subtask_id;
        $oldTaskType  = $task->task_type;
        $oldStatus    = $task->status;

        $updateData = [
            'title'       => $request->title,
            'task_type'   => $request->task_type,
            'subtask_id'  => $request->subtask_id,
            'status'      => $request->status,
            'from_date'   => $request->from_date,
            'to_date'     => $request->to_date,
            'description' => $request->description,
            // 'user_id'     => auth()->id(),
        ];

        if ($request->task_type == 'individual') {
            $updateData['mode'] = null;
            $updateData['location_id'] = null;
            $updateData['group_id'] = null;
        }

        if ($request->task_type == 'location') {

        $updateData['mode'] = $request->mode;
    
        if ($request->mode == 'location_wise') {
    
            $updateData['location_id'] = $request->location_id;
            $updateData['group_id'] = null;
        }
    
        if ($request->mode == 'group_team') {
    
            $updateData['group_id'] = $request->group_id;
            $updateData['location_id'] = null;
        }
    }

        $task->update($updateData);

        // Subtask change remark
        if ($oldSubtaskId != $request->subtask_id) {
            $oldSubtaskObj = $oldSubtaskId ? \App\Models\Subtask::find($oldSubtaskId) : null;
            $oldSubtaskName = $oldSubtaskObj ? $oldSubtaskObj->subtask : 'None';
            $newSubtaskObj = $request->subtask_id ? \App\Models\Subtask::find($request->subtask_id) : null;
            $newSubtaskName = $newSubtaskObj ? $newSubtaskObj->subtask : 'None';

            if ($oldSubtaskName !== $newSubtaskName) {
                Activity::create([
                    'user_id'     => Auth::id(),
                    'table_name'  => 'tasks',
                    'table_id'    => $task->id,
                    'operation'   => 'subtask_change',
                    'description' => 'Task "' . $task->title . '" subtask changed from ' . $oldSubtaskName . ' to ' . $newSubtaskName,
                ]);

                $this->sendTaskNotification(
                    $task,
                    'Task "' . $task->title . '" subtask changed from ' . $oldSubtaskName . ' to ' . $newSubtaskName . '.'
                );
            }
        }

        // Task type change remark
        if ($oldTaskType && $request->task_type && $oldTaskType !== $request->task_type) {
            Activity::create([
                'user_id'     => Auth::id(),
                'table_name'  => 'tasks',
                'table_id'    => $task->id,
                'operation'   => 'update',
                'description' => 'Task "' . $task->title . '": Task type changed from ' . ucfirst($oldTaskType) . ' to ' . ucfirst($request->task_type),
            ]);
        }

        // Status change remark
        if ($oldStatus && $request->status && $oldStatus !== $request->status) {
            $fromLabel = ucfirst(str_replace('_', ' ', $oldStatus));
            $toLabel   = ucfirst(str_replace('_', ' ', $request->status));

            Activity::create([
                'user_id'     => Auth::id(),
                'table_name'  => 'tasks',
                'table_id'    => $task->id,
                'operation'   => 'status_change',
                'description' => 'Task "' . $task->title . '" status changed from ' . $fromLabel . ' to ' . $toLabel,
            ]);
        }
       

        /* EMPLOYEE ASSIGNMENT */
        $employeesToAssign = [];

        if ($request->task_type == 'individual') {
            $employeesToAssign = $request->assigned_to ?? [];
        }

        if ($request->task_type == 'location') {

            if ($request->mode == 'location_wise') {
                $employeesToAssign = $request->assigned_to ?? [];
            }

            if ($request->mode == 'group_team') {

            if (!empty($request->group_id)) {
        
                $employeesToAssign = \DB::table('group_employees')
                    ->join('employees', 'group_employees.employee_id', '=', 'employees.id')
                    ->join('users', 'employees.user_id', '=', 'users.id')
                    ->where('group_employees.group_id', $request->group_id)
                    ->where('users.is_active', 1)
                    ->whereDate('group_employees.joined_at', '<=', $request->to_date)
                    ->where(function ($query) use ($request) {
                        $query->whereNull('group_employees.removed_at')
                            ->orWhereDate('group_employees.removed_at', '>=', $request->to_date);
                    })
                    ->pluck('group_employees.employee_id')
                    ->unique()
                    ->toArray();
            }
        }
        }

        $employeesToAssign = array_values(array_unique(array_filter($employeesToAssign)));

        $task->taskUsers()->delete();

        if (!empty($employeesToAssign)) {

            $insert = [];

            foreach ($employeesToAssign as $empId) {

                $insert[] = [
                    'task_id'     => $task->id,
                    'assigned_to' => $empId,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }

            \DB::table('task_users')->insert($insert);
        }

        Activity::create([
            'user_id'     => Auth::id(),
            'table_name'  => 'tasks',
            'table_id'    => $task->id,
            'operation'   => 'update',
            'description' => 'Task "'.$task->title.'" updated',
        ]);

        /* =========================================
            NOTIFICATION
        ========================================= */

            $this->sendTaskNotification(
                $task,
                'Task "' . $task->title . '" has been updated.'
            );
       

        return redirect()->route('tasks.index')
            ->with('success', 'Task updated successfully');
    }

    /* =======================================
       DELETE
    ======================================= */

    
    public function destroy($id)
{
    $user = Auth::user();
    if (!(
        $user->user_type == 'admin' ||
        hasPermission('task_delete') ||
        hasPermission('task_edit') ||
        hasPermission('task_view_all') ||
        hasPermission('task_view_own')
    )) {
        return response()->view('errors.no-access');
    }

    $task = Task::findOrFail($id);

    \DB::beginTransaction();

    try {

        // Activity Log
        Activity::create([
            'user_id'     => Auth::id(),
            'table_name'  => 'tasks',
            'table_id'    => $task->id,
            'operation'   => 'delete',
            'description' => 'Task "'.$task->title.'" deleted',
        ]);

        
        if ($task->dashboard_task_id) {
            \DB::table('dashboard_tasks')
                ->where('id', $task->dashboard_task_id)
                ->delete();
        }

        
        $task->taskUsers()->delete();

       
        $task->delete();

        \DB::commit();

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Task deleted successfully');

    } catch (\Exception $e) {

        \DB::rollback();

        return back()->with('error', 'Something went wrong');
    }
}
    
    // TaskController.php

    /* =======================================
       CHANGE STATUS
    ======================================= */
    public function changeStatus(Request $request, $id)
    {
        if (!hasPermission('task_change_status')) {
            return response()->json([
                'status' => false,
                'message' => 'No permission'
            ]);
        }
    
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed'
        ]);
    
        $task = Task::findOrFail($id);
    
        $oldStatus = $task->status;
    
        $task->update([
            'status' => $request->status
        ]);
    
        if ($oldStatus !== $request->status) {
            $fromLabel = ucfirst(str_replace('_', ' ', $oldStatus));
            $toLabel   = ucfirst(str_replace('_', ' ', $request->status));

            Activity::create([
                'user_id'     => Auth::id(),
                'table_name'  => 'tasks',
                'table_id'    => $task->id,
                'operation'   => 'status_change',
                'description' => 'Task "' . $task->title . '" status changed from ' . $fromLabel . ' to ' . $toLabel,
            ]);

            $this->sendTaskNotification(
                $task,
                'Task "' . $task->title . '" status changed from ' . $fromLabel . ' to ' . $toLabel . '.'
            );
        }
    
        return response()->json([
            'status' => true,
            'message' => 'Task status updated successfully'
        ]);
    }
    
    /* =======================================
       CHANGE SUBTASK
    ======================================= */
    public function changeSubtask(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $user = Auth::user();
        
        $employeeId = \App\Models\Employee::where('user_id', $user->id)->value('id');
        $isAssigned = $task->taskUsers->where('assigned_to', $employeeId)->count() > 0;
        $isCreator = $task->user_id == $user->id;

        if (!(
            $user->user_type == 'admin' ||
            hasPermission('task_change_subtask_status')
        )) {
            return response()->json([
                'status' => false,
                'message' => 'No permission'
            ]);
        }
    
        $request->validate([
            'subtask_id' => 'nullable|exists:subtasks,id'
        ]);
    
        $task = Task::findOrFail($id);
        
        $oldSubtask = $task->subtask_id ? \App\Models\Subtask::find($task->subtask_id) : null;
        $oldSubtaskName = $oldSubtask ? $oldSubtask->subtask : 'None';
        
        $newSubtask = $request->subtask_id ? \App\Models\Subtask::find($request->subtask_id) : null;
        $newSubtaskName = $newSubtask ? $newSubtask->subtask : 'None';
    
        $task->update([
            'subtask_id' => $request->subtask_id
        ]);
    
        if ($oldSubtaskName !== $newSubtaskName) {
            Activity::create([
                'user_id'     => Auth::id(),
                'table_name'  => 'tasks',
                'table_id'    => $task->id,
                'operation'   => 'subtask_change',
                'description' => 'Task "' . $task->title . '" subtask changed from ' . $oldSubtaskName . ' to ' . $newSubtaskName,
            ]);

            $this->sendTaskNotification(
                $task,
                'Task "' . $task->title . '" subtask changed from '.$oldSubtaskName.' to ' . $newSubtaskName . '.'
            );
        }
    
        return response()->json([
            'status' => true,
            'message' => 'Sub Task updated successfully'
        ]);
    }
    
    public function storeRemark(Request $request, $id)
{
    $request->validate([
        'remark' => 'required|string|max:1000',
    ]);

    $task = Task::findOrFail($id);

    Activity::create([
        'user_id' => Auth::id(),
        'table_id' => $task->id,
        'table_name' => 'tasks',
        'operation' => 'remark',
        'description' => 'Remark added on task "' . $task->title . '": ' . $request->remark,
    ]);

    $this->sendTaskNotification(
        $task,
        'Remark added on task "' . $task->title . '": ' . $request->remark
    );

     return response()->json([
        'status' => true,
        'message' => 'Remark added successfully'
    ]);
}


// notification
private function sendTaskNotification(Task $task, string $message)
{
    $currentUser = Auth::user();
    $usersToNotify = collect();

    // 1. Add Task Creator
    if ($task->user_id) {
        $creator = User::find($task->user_id);
        if ($creator && $creator->id !== $currentUser->id) {
            $usersToNotify->push($creator);
        }
    }

    // 2. Add Assigned Employees
    $task->load('taskUsers.employee');
    foreach ($task->taskUsers as $taskUser) {
        if ($taskUser->employee && $taskUser->employee->user_id) {
            $assignedUser = User::find($taskUser->employee->user_id);
            if ($assignedUser && $assignedUser->id !== $currentUser->id) {
                $usersToNotify->push($assignedUser);
            }
        }
    }

    // 3. Add Admins
    $admins = User::where('user_type', 'admin')->get();
    foreach ($admins as $admin) {
        if ($admin->id !== $currentUser->id) {
            $usersToNotify->push($admin);
        }
    }

    // Ensure unique users to avoid duplicate notifications
    $usersToNotify = $usersToNotify->unique('id');

    // Send Notification
    foreach ($usersToNotify as $user) {
        $user->notify(new TaskUpdatedNotification($task, $message));
    }
}
}