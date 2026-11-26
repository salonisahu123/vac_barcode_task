<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketAssignment;
use App\Models\TicketComment;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Location;
use App\Models\Group;
use App\Models\Activity;
use App\Models\GroupEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | HELPER: Check if employee is complained against in this ticket
    |--------------------------------------------------------------------------
    */
    private function isComplainedAgainst($ticket, $user = null): bool
    {
        $user = $user ?: auth()->user();
        if (!$user) {
            return false;
        }

        $employeeId = Employee::where('user_id', $user->id)->value('id');
        if (!$employeeId) {
            return false;
        }

        $myGroupIds = GroupEmployee::where('employee_id', $employeeId)->pluck('group_id')->toArray();

        $ticket->loadMissing('assignments');

        return $ticket->assignments->contains(function ($a) use ($employeeId, $myGroupIds) {
            return $a->assigned_to == $employeeId 
                || (!empty($a->group_id) && in_array($a->group_id, $myGroupIds));
        });
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER: Complaint Access Query Scope
    |--------------------------------------------------------------------------
    | 1. Super Admin & users with ticket_view_all: see all complaints.
    | 2. Creator (ticket_view_own / ticket_add): sees complaints they created.
    | 3. STRICT RULE: Any complaint filed against this user is NEVER shown.
    */
    private function applyComplaintScope($query, $user)
    {
        $isAdmin = $user->user_type == 'admin';
        $employeeId = Employee::where('user_id', $user->id)->value('id');

        if ($isAdmin || hasPermission('ticket_view_all')) {
            // Super Admin and users with ticket_view_all see all
        } elseif (hasPermission('ticket_view_own') || hasPermission('ticket_add')) {
            // Users who added complaints see only their own
            $query->where('tickets.user_id', $user->id);
        } else {
            $query->whereRaw('1 = 0');
        }

        // Under no circumstances can a person see complaints filed against themselves
        if ($employeeId) {
            $myGroupIds = GroupEmployee::where('employee_id', $employeeId)->pluck('group_id')->toArray();

            $query->whereDoesntHave('assignments', function ($q) use ($employeeId, $myGroupIds) {
                $q->where(function ($sub) use ($employeeId, $myGroupIds) {
                    $sub->where('assigned_to', $employeeId);
                    if (!empty($myGroupIds)) {
                        $sub->orWhereIn('group_id', $myGroupIds);
                    }
                });
            });
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX - Employee Complaints List
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->user_type == 'admin' || hasPermission('ticket_view_all');

        if (!$isAdmin && !hasPermission('ticket_view_own') && !hasPermission('ticket_add')) {
            return response()->view('errors.no-access', [], 403);
        }

        $query = Ticket::with([
            'creator',
            'assignments.employee.user',
            'assignments.group',
            'department',
            'location'
        ]);

        $this->applyComplaintScope($query, $user);

        // Status Filter: default to 'active' (excluding closed complaints)
        $selectedStatus = $request->get('status', 'active');
        if ($selectedStatus === 'active') {
            $query->where('tickets.status', '!=', 'closed');
        } elseif ($selectedStatus !== 'all' && !empty($selectedStatus)) {
            $query->where('tickets.status', $selectedStatus);
        }

        if ($request->filled('location_id') && $request->location_id !== 'all') {
            $query->where('location_id', $request->location_id);
        }

        if ($request->filled('group_id') && $request->group_id !== 'all') {
            $query->whereHas('assignments', function ($q) use ($request) {
                $q->where('group_id', $request->group_id);
            });
        }

        $tickets = $query->latest()->get();

        $employees = Employee::with('user')->whereHas('user', function ($q) {
            $q->where('is_active', 1);
        })->get();

        $groups = Group::all();
        $locations = Location::all();

        $statusOptions = [
            'pending',
            'assigned',
            'in_progress',
            'hold',
            'close_request',
            'closed',
            'reopen'
        ];

        $loginUserId = Auth::id();
        $canManageStatus = $isAdmin || hasPermission('ticket_change_status');

        foreach ($tickets as $ticket) {
            $isCreatedByMe = $ticket->user_id == $loginUserId;

            $ticket->can_edit_status = [
                'can_manage' => $canManageStatus,
                'created_by_me' => $isCreatedByMe,
                'status' => $ticket->status
            ];
        }

        return view('tickets.index', compact(
            'tickets',
            'employees',
            'groups',
            'locations',
            'statusOptions',
            'selectedStatus',
            'isAdmin'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | FILTER - Ajax Filter for Complaints
    |--------------------------------------------------------------------------
    */
    public function filter(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['html' => '']);
        }

        $isAdmin = $user->user_type == 'admin' || hasPermission('ticket_view_all');

        if (!$isAdmin && !hasPermission('ticket_view_own') && !hasPermission('ticket_add')) {
            return response()->json(['html' => '']);
        }

        $query = Ticket::with([
            'creator',
            'location',
            'department',
            'assignments.employee.user',
            'assignments.group'
        ]);

        $this->applyComplaintScope($query, $user);

        // Status Filter: default to 'active' (excluding closed complaints)
        $status = $request->get('status', 'active');
        if ($status === 'active') {
            $query->where('tickets.status', '!=', 'closed');
        } elseif ($status && $status !== 'all') {
            $query->where('tickets.status', $status);
        }

        if ($request->filled('location_id') && $request->location_id !== 'all') {
            $query->where('location_id', $request->location_id);
        }

        if ($request->filled('group_id') && $request->group_id !== 'all') {
            $query->whereHas('assignments', function ($q) use ($request) {
                $q->where('group_id', $request->group_id);
            });
        }

        $tickets = $query->latest()->get();

        $loginUserId = Auth::id();
        $canManageStatus = $isAdmin || hasPermission('ticket_change_status');

        foreach ($tickets as $ticket) {
            $isCreatedByMe = $ticket->user_id == $loginUserId;

            $ticket->can_edit_status = [
                'can_manage' => $canManageStatus,
                'created_by_me' => $isCreatedByMe,
                'status' => $ticket->status
            ];
        }

        $viewIsAdmin = $isAdmin;

        return response()->json([
            'html' => view('tickets.partials.rows', [
                'tickets' => $tickets,
                'isAdmin' => $viewIsAdmin
            ])->render()
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE - File a New Complaint
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $user = Auth::user();
        if ($user->user_type !== 'admin' && !hasPermission('ticket_add')) {
            return response()->view('errors.no-access', [], 403);
        }

        $departments = Department::orderBy('dept_name')->get();
        $locations   = Location::orderBy('name')->get();
        $employees   = Employee::with('user')->whereHas('user', function ($q) {
            $q->where('is_active', 1);
        })->get();

        return view('tickets.create', compact('departments', 'locations', 'employees'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE - Save Complaint & Assign Complained Employee
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->user_type !== 'admin' && !hasPermission('ticket_add')) {
            return response()->view('errors.no-access', [], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'ticket_type' => 'required|in:individual,location',
            'department_id' => 'required|exists:departments,id',
            'subject' => 'required|string|max:255',
            'priority' => 'required|in:low,medium,high',
            'description' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,txt,zip|max:15360',
            'location_id' => 'required_if:ticket_type,location|nullable|exists:locations,id',
            'assigned_to' => 'nullable|array',
            'assigned_to.*' => 'exists:employees,id',
        ]);

        $fileName = null;

        if ($request->hasFile('attachment')) {
            $targetDir = public_path('uploads/tickets');
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $cleanName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $request->attachment->getClientOriginalName());
            $fileName = time() . '_' . $cleanName;
            $request->attachment->move($targetDir, $fileName);
        }

        $hasAssignments = !empty($request->assigned_to);

        $ticket = Ticket::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'ticket_type' => $request->ticket_type,
            'location_id' => $request->ticket_type == 'location' ? $request->location_id : null,
            'department_id' => $request->department_id,
            'subject' => $request->subject,
            'priority' => $request->priority,
            'description' => $request->description,
            'attachment' => $fileName,
            'status' => $hasAssignments ? 'assigned' : 'pending',
        ]);

        if ($hasAssignments) {
            foreach ($request->assigned_to as $empId) {
                $assignment = TicketAssignment::create([
                    'ticket_id'   => $ticket->id,
                    'assigned_by' => Auth::id(),
                    'assign_type' => 'individual',
                    'assigned_to' => $empId,
                    'location_id' => $ticket->location_id,
                    'assigned_at' => now(),
                ]);

                $emp = Employee::with('user')->find($empId);
                $empName = $emp->user->name ?? "Employee #{$empId}";

                Activity::create([
                    'user_id' => Auth::id(),
                    'table_name' => 'ticket_assignments',
                    'table_id' => $assignment->id,
                    'operation' => 'assign',
                    'description' => "Complaint filed against: {$empName}"
                ]);
            }
        }

        Activity::create([
            'user_id' => Auth::id(),
            'table_name' => 'tickets',
            'table_id' => $ticket->id,
            'operation' => 'create',
            'description' => 'Complaint filed: ' . $ticket->title
        ]);

        return redirect()->route('tickets.index')
            ->with('success', 'Complaint filed successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW - Complaint Details (Confidential to Complained Employee)
    |--------------------------------------------------------------------------
    /*
    |--------------------------------------------------------------------------
    | SHOW - Complaint Details
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $user = Auth::user();
        $isAdmin = $user->user_type == 'admin';

        $ticket = Ticket::with([
            'creator',
            'assignments.employee.user',
            'comments.user.employee.department',
            'comments.user.employee.role',
            'department',
            'location'
        ])->findOrFail($id);

        // ACCESS CONTROL: The employee being complained against MUST NOT see the complaint under any circumstances
        if ($this->isComplainedAgainst($ticket, $user)) {
            return response()->view('errors.no-access', [], 403);
        }

        $canView = $isAdmin
            || hasPermission('ticket_view_all')
            || (($ticket->user_id == $user->id) && (hasPermission('ticket_view_own') || hasPermission('ticket_add')));

        if (!$canView) {
            return response()->view('errors.no-access', [], 403);
        }

        // Activity timeline with users
        $query = Activity::with('user.employee.department');
        $query->where(function ($q) use ($ticket) {
            $q->where('table_name', 'tickets')
                ->where('table_id', $ticket->id);
        });
        $query->orWhere(function ($q) use ($ticket) {
            $q->where('table_name', 'ticket_assignments')
                ->whereIn('table_id', function ($sub) use ($ticket) {
                    $sub->select('id')
                        ->from('ticket_assignments')
                        ->where('ticket_id', $ticket->id);
                });
        });
        $query->orWhere(function ($q) use ($ticket) {
            $q->where('table_name', 'ticket_comments')
                ->whereIn('table_id', function ($sub) use ($ticket) {
                    $sub->select('id')
                        ->from('ticket_comments')
                        ->where('ticket_id', $ticket->id);
                });
        });

        $activities = $query->orderBy('created_at', 'desc')->get();

        foreach ($activities as $act) {
            if ($act->table_name == 'ticket_assignments') {
                $assign = TicketAssignment::with('employee.user')->find($act->table_id);
                $name = $assign->employee->user->name ?? 'Unknown';
                $act->description = "Complaint assigned against: $name";
            } elseif ($act->table_name == 'ticket_comments') {
                if (empty($act->description) || $act->description == 'Comment added on complaint') {
                    $comment = TicketComment::with('user.employee.department')->find($act->table_id);
                    if ($comment) {
                        $actorName = $comment->user->name ?? 'User';
                        $dept = $comment->user->employee->department->dept_name ?? ($comment->user->user_type == 'admin' ? 'Admin' : '');
                        $deptTag = $dept ? " ({$dept})" : '';

                        if ($comment->attachment) {
                            $cleanFile = preg_replace('/^\d+_/', '', $comment->attachment);
                            $act->description = "{$actorName}{$deptTag} shared file '{$cleanFile}'" . (filled(trim($comment->comment)) ? " with message: \"" . \Illuminate\Support\Str::limit($comment->comment, 40) . "\"" : "");
                        } else {
                            $act->description = "{$actorName}{$deptTag} posted: \"" . \Illuminate\Support\Str::limit($comment->comment, 50) . "\"";
                        }
                    }
                }
            }
        }

        return view('tickets.show', compact('ticket', 'activities'));
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT - Edit Complaint
    |--------------------------------------------------------------------------
    */
    public function edit($id)
    {
        $user = Auth::user();
        $isAdmin = $user->user_type == 'admin';

        $ticket = Ticket::with('assignments')->findOrFail($id);

        // Complained employee cannot edit
        if ($this->isComplainedAgainst($ticket, $user)) {
            return response()->view('errors.no-access', [], 403);
        }

        $canEdit = $isAdmin
            || hasPermission('ticket_edit')
            || (($ticket->user_id == $user->id) && (hasPermission('ticket_view_own') || hasPermission('ticket_add')));

        if (!$canEdit) {
            return response()->view('errors.no-access', [], 403);
        }

        $locations = Location::all();
        $departments = Department::all();
        $employees = Employee::with('user')->whereHas('user', function ($q) {
            $q->where('is_active', 1);
        })->get();

        return view('tickets.edit', compact(
            'ticket',
            'locations',
            'departments',
            'employees'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE - Update Complaint
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $isAdmin = $user->user_type == 'admin';

        $ticket = Ticket::with('assignments')->findOrFail($id);

        if ($this->isComplainedAgainst($ticket, $user)) {
            return response()->view('errors.no-access', [], 403);
        }

        $canEdit = $isAdmin
            || hasPermission('ticket_edit')
            || (($ticket->user_id == $user->id) && (hasPermission('ticket_view_own') || hasPermission('ticket_add')));

        if (!$canEdit) {
            return response()->view('errors.no-access', [], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'ticket_type' => 'required|in:individual,location',
            'department_id' => 'required|exists:departments,id',
            'subject' => 'required|string|max:255',
            'priority' => 'required|in:low,medium,high',
            'description' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
            'location_id' => 'required_if:ticket_type,location|nullable|exists:locations,id',
            'assigned_to' => 'nullable|array',
            'assigned_to.*' => 'exists:employees,id',
        ]);

        if ($request->hasFile('attachment')) {
            if ($ticket->attachment && file_exists(public_path('uploads/tickets/' . $ticket->attachment))) {
                @unlink(public_path('uploads/tickets/' . $ticket->attachment));
            }

            $targetDir = public_path('uploads/tickets');
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $cleanName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $request->attachment->getClientOriginalName());
            $fileName = time() . '_' . $cleanName;
            $request->attachment->move($targetDir, $fileName);
            $ticket->attachment = $fileName;
        }

        $ticket->update([
            'title' => $request->title,
            'ticket_type' => $request->ticket_type,
            'department_id' => $request->department_id,
            'subject' => $request->subject,
            'priority' => $request->priority,
            'description' => $request->description,
            'location_id' => $request->ticket_type == 'location' ? $request->location_id : null,
            'attachment' => $ticket->attachment,
        ]);

        // If individual complaint and assigned_to is supplied
        if ($request->ticket_type == 'individual' && $request->has('assigned_to')) {
            TicketAssignment::where('ticket_id', $ticket->id)->delete();
            if (!empty($request->assigned_to)) {
                foreach ($request->assigned_to as $empId) {
                    TicketAssignment::create([
                        'ticket_id'   => $ticket->id,
                        'assigned_by' => Auth::id(),
                        'assign_type' => 'individual',
                        'assigned_to' => $empId,
                        'location_id' => $ticket->location_id,
                        'assigned_at' => now(),
                    ]);
                }
                if ($ticket->status == 'pending') {
                    $ticket->update(['status' => 'assigned']);
                }
            }
        }

        Activity::create([
            'user_id' => Auth::id(),
            'table_name' => 'tickets',
            'table_id' => $ticket->id,
            'operation' => 'update',
            'description' => 'Complaint updated: ' . $ticket->title
        ]);

        return redirect()->route('tickets.index')
            ->with('success', 'Complaint updated successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY - Delete Complaint
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $user = Auth::user();
        $isAdmin = $user->user_type == 'admin';

        if (!$isAdmin && !hasPermission('ticket_delete')) {
            return response()->view('errors.no-access', [], 403);
        }

        $ticket = Ticket::findOrFail($id);

        if ($this->isComplainedAgainst($ticket, $user)) {
            return response()->view('errors.no-access', [], 403);
        }

        if ($ticket->attachment && file_exists(public_path('uploads/tickets/' . $ticket->attachment))) {
            unlink(public_path('uploads/tickets/' . $ticket->attachment));
        }

        $title = $ticket->title;
        $ticket->delete();

        Activity::create([
            'user_id' => Auth::id(),
            'table_name' => 'tickets',
            'table_id' => $id,
            'operation' => 'delete',
            'description' => 'Complaint deleted: ' . $title
        ]);

        return back()->with('success', 'Complaint deleted successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | ASSIGN - Assign / Set Complained Employee(s) or Group
    |--------------------------------------------------------------------------
    */
    public function assign(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->user_type == 'admin';

        if (!$isAdmin && !hasPermission('ticket_assign') && !hasPermission('ticket_change_status') && !hasPermission('ticket_view_all')) {
            return response()->json([
                'success' => false,
                'message' => 'No Access'
            ], 403);
        }

        $ticket = Ticket::findOrFail($request->ticket_id);

        if ($this->isComplainedAgainst($ticket, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'No Access'
            ], 403);
        }

        $ticket->update([
            'status' => 'assigned'
        ]);

        TicketAssignment::where('ticket_id', $ticket->id)->delete();

        /* INDIVIDUAL ASSIGN */
        if (!empty($request->employee_id)) {
            foreach ($request->employee_id as $emp) {
                $assignment = TicketAssignment::create([
                    'ticket_id'   => $ticket->id,
                    'assigned_by' => auth()->id(),
                    'assign_type' => $request->assign_type ?? 'individual',
                    'assigned_to' => $emp,
                    'location_id' => $ticket->location_id,
                    'assigned_at' => now(),
                ]);

                $empModel = Employee::with('user')->find($emp);
                $empName = $empModel->user->name ?? "Employee #{$emp}";

                Activity::create([
                    'user_id' => Auth::id(),
                    'table_name' => 'ticket_assignments',
                    'table_id' => $assignment->id,
                    'operation' => 'assign',
                    'description' => 'Complaint assigned against: ' . $empName
                ]);
            }
        }

        /* GROUP ASSIGN */
        if (!empty($request->group_id)) {
            $employees = GroupEmployee::where('group_id', $request->group_id)->get();

            foreach ($employees as $row) {
                $assignment = TicketAssignment::create([
                    'ticket_id'   => $ticket->id,
                    'assigned_by' => auth()->id(),
                    'assign_type' => 'group_team',
                    'assigned_to' => $row->employee_id,
                    'location_id' => $ticket->location_id,
                    'group_id'    => $request->group_id,
                    'assigned_at' => now(),
                ]);

                Activity::create([
                    'user_id' => Auth::id(),
                    'table_name' => 'ticket_assignments',
                    'table_id' => $assignment->id,
                    'operation' => 'assign',
                    'description' => 'Complaint assigned against group ID: ' . $request->group_id
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Complained employee assigned successfully'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER: Format Comment For Response (WhatsApp / Chat with Metadata)
    |--------------------------------------------------------------------------
    */
    private function formatCommentForResponse($c, $currentUserId)
    {
        $isMe = ($c->user_id == $currentUserId);
        $emp = $c->user->employee ?? null;
        $avatar = ($emp && $emp->image && file_exists(public_path('uploads/employees/' . $emp->image)))
            ? asset('uploads/employees/' . $emp->image)
            : asset('uploads/employees/user.png');

        $roleOrDept = '';
        if ($emp && $emp->department) {
            $roleOrDept = $emp->department->dept_name;
        } elseif ($c->user && $c->user->user_type == 'admin') {
            $roleOrDept = 'Admin';
        }

        $ext = $c->attachment ? strtolower(pathinfo($c->attachment, PATHINFO_EXTENSION)) : null;
        $displayName = $c->attachment ? preg_replace('/^\d+_/', '', $c->attachment) : '';

        // Calculate file size if file exists on disk
        $fileSizeFormatted = null;
        if ($c->attachment && file_exists(public_path('uploads/comments/' . $c->attachment))) {
            $bytes = @filesize(public_path('uploads/comments/' . $c->attachment));
            if ($bytes !== false) {
                if ($bytes < 1024 * 1024) {
                    $fileSizeFormatted = round($bytes / 1024, 1) . ' KB';
                } else {
                    $fileSizeFormatted = round($bytes / (1024 * 1024), 1) . ' MB';
                }
            }
        }

        $createdAt = $c->created_at;
        $dateDivider = $createdAt->isToday() 
            ? 'Today' 
            : ($createdAt->isYesterday() ? 'Yesterday' : $createdAt->format('d M Y'));

        return [
            'id'            => $c->id,
            'user_id'       => $c->user_id,
            'user_name'     => $c->user->name ?? 'User',
            'user_dept'     => $roleOrDept,
            'avatar'        => $avatar,
            'comment'       => $c->comment ?? '',
            'message'       => $c->comment ?? '',
            'attachment'    => $c->attachment,
            'file'          => $c->attachment ? asset('uploads/comments/' . $c->attachment) : null,
            'file_name'     => $displayName,
            'file_ext'      => $ext,
            'file_size'     => $fileSizeFormatted,
            'is_image'      => in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']),
            'time'          => $createdAt->format('g:i A'),
            'date'          => $createdAt->format('d M Y'),
            'date_divider'  => $dateDivider,
            'full_datetime' => $createdAt->format('d M Y, h:i A'),
            'relative_time' => $createdAt->diffForHumans(),
            'is_me'         => $isMe,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | ADD COMMENT / CHAT MESSAGE
    |--------------------------------------------------------------------------
    */
    public function addComment(Request $request, $id)
    {
        try {
            $ticket = Ticket::with('assignments')->findOrFail($id);
            $user = auth()->user();
            $isAdmin = $user->user_type == 'admin';

            // Complained employee cannot comment under any circumstances
            if ($this->isComplainedAgainst($ticket, $user)) {
                return response()->json(['success' => false, 'error' => 'No access to this complaint.'], 403);
            }

            $canComment = $isAdmin
                || hasPermission('ticket_view_all')
                || (($ticket->user_id == $user->id) && (hasPermission('ticket_view_own') || hasPermission('ticket_add')));

            if (!$canComment) {
                return response()->json(['success' => false, 'error' => 'No access to this complaint.'], 403);
            }

            $hasComment = filled(trim($request->comment ?? ''));
            $hasFile = $request->hasFile('attachment');

            if (!$hasComment && !$hasFile) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Please type a message or select a file to send.'
                ], 422);
            }

            $fileName = null;
            $originalName = null;

            if ($hasFile) {
                $request->validate([
                    'attachment' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,bmp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,csv|max:15360'
                ]);

                $file = $request->file('attachment');
                $originalName = $file->getClientOriginalName();
                $sanitized = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                $fileName = time() . '_' . $sanitized;

                $targetDir = public_path('uploads/comments');
                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $file->move($targetDir, $fileName);
            }

            $comment = TicketComment::create([
                'ticket_id'  => $id,
                'user_id'    => $user->id,
                'comment'    => $request->comment ?? '',
                'attachment' => $fileName
            ]);

            $emp = Employee::with('department')->where('user_id', $user->id)->first();
            $actorName = $user->name ?? 'User';
            $deptName = $emp->department->dept_name ?? ($user->user_type == 'admin' ? 'Admin' : '');
            $deptTag = $deptName ? " ({$deptName})" : '';
            $displayName = $originalName ?: ($fileName ? preg_replace('/^\d+_/', '', $fileName) : '');

            $actionDesc = "{$actorName}{$deptTag} ";
            if ($fileName && filled(trim($request->comment ?? ''))) {
                $actionDesc .= "shared file '{$displayName}' and sent message: \"" . \Illuminate\Support\Str::limit($request->comment, 40) . "\"";
            } elseif ($fileName) {
                $actionDesc .= "shared file '{$displayName}'";
            } else {
                $actionDesc .= "posted: \"" . \Illuminate\Support\Str::limit($request->comment, 50) . "\"";
            }

            Activity::create([
                'user_id'     => $user->id,
                'table_name'  => 'ticket_comments',
                'table_id'    => $comment->id,
                'operation'   => 'comment',
                'description' => $actionDesc
            ]);

            $comment->load(['user.employee.department']);
            $formatted = $this->formatCommentForResponse($comment, $user->id);
            $formatted['success'] = true;

            return response()->json($formatted);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'success' => false,
                'error'   => $ve->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GET COMMENTS / CHAT MESSAGES
    |--------------------------------------------------------------------------
    */
    public function getComments($id)
    {
        $ticket = Ticket::with('assignments')->findOrFail($id);
        $user = auth()->user();
        $isAdmin = $user->user_type == 'admin';

        if ($this->isComplainedAgainst($ticket, $user)) {
            return response()->json([]);
        }

        $canView = $isAdmin
            || hasPermission('ticket_view_all')
            || (($ticket->user_id == $user->id) && (hasPermission('ticket_view_own') || hasPermission('ticket_add')));

        if (!$canView) {
            return response()->json([]);
        }

        $comments = TicketComment::with(['user.employee.department'])
            ->where('ticket_id', $id)
            ->orderBy('id', 'asc')
            ->get();

        $data = $comments->map(function ($c) use ($user) {
            return $this->formatCommentForResponse($c, $user->id);
        });

        return response()->json($data);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE STATUS
    |--------------------------------------------------------------------------
    */
    public function updateStatus(Request $request)
    {
        $ticket = Ticket::with('assignments')->findOrFail($request->ticket_id);
        $user = auth()->user();
        $isAdmin = $user->user_type == 'admin';

        // Complained employee cannot update status
        if ($this->isComplainedAgainst($ticket, $user)) {
            return response()->json(['success' => false, 'message' => 'No access'], 403);
        }

        $canManage = $isAdmin || hasPermission('ticket_change_status') || hasPermission('ticket_view_all');
        $isCreator = $ticket->user_id == $user->id;

        if (!$canManage && !($isCreator && $ticket->status == 'close_request')) {
            return response()->json(['success' => false, 'message' => 'Not allowed'], 403);
        }

        $newStatus = $request->status;

        // Creator rule
        if (!$canManage && $isCreator && $ticket->status == 'close_request') {
            if (!in_array($newStatus, ['closed', 'reopen'])) {
                return response()->json(['success' => false, 'message' => 'Not allowed'], 403);
            }
        }

        if ($newStatus == 'reopen') {
            $newStatus = 'pending';

            Activity::create([
                'user_id' => Auth::id(),
                'table_name' => 'tickets',
                'table_id' => $ticket->id,
                'operation' => 'reopen',
                'description' => 'Complaint reopened, status set back to pending'
            ]);
        }

        $ticket->status = $newStatus;
        $ticket->save();

        Activity::create([
            'user_id' => Auth::id(),
            'table_name' => 'tickets',
            'table_id' => $ticket->id,
            'operation' => 'status_update',
            'description' => 'Status changed to ' . $newStatus
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS OPTIONS
    |--------------------------------------------------------------------------
    */
    public function statusOptions(Request $request)
    {
        $ticket = Ticket::with('assignments')->findOrFail($request->ticket_id);
        $user = auth()->user();

        $options = $this->getTicketStatusOptions($ticket, $user);

        return response()->json([
            'options' => $options
        ]);
    }

    public function getTicketStatusOptions($ticket, $user)
    {
        $isAdmin = $user->user_type == 'admin';

        // Complained employee gets no options
        if ($this->isComplainedAgainst($ticket, $user)) {
            return [];
        }

        $canManage = $isAdmin || hasPermission('ticket_change_status') || hasPermission('ticket_view_all');
        $isCreator = $ticket->user_id == $user->id;
        $status = $ticket->status;

        $options = [];

        if ($canManage) {
            $allPossible = ['pending', 'assigned', 'in_progress', 'hold', 'close_request', 'closed', 'reopen'];
            $options = array_values(array_diff($allPossible, [$status]));
        } elseif ($isCreator && $status == 'close_request') {
            $options = ['closed', 'reopen'];
        }

        return $options;
    }
}
