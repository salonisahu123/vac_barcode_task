@forelse($tasks as $task)
<tr id="task-row-{{ $task->id }}" style="font-size:13px; transition: background 0.2s ease;">

    <td class="text-center font-weight-500" style="color:#64748b; width:45px;">
        {{ method_exists($tasks, 'firstItem') ? (($tasks->firstItem() ?? 1) + $loop->index) : $loop->iteration }}
    </td>

    <td style="min-width:180px;">
        <span style="font-weight:600; color:#1e293b; display:block; line-height:1.4;">
            {{ $task->title }}
        </span>
        @if(!empty($task->latest_remark_text) && !str_starts_with(strtolower($task->latest_remark_text), 'status changed'))
            @php
                $remarkUser = $task->latestRemark?->user?->name ?? 'User';
                $remarkDate = $task->latestRemark?->created_at ? \Carbon\Carbon::parse($task->latestRemark->created_at) : null;
                $humanTime = $remarkDate ? ($remarkDate->isToday() ? 'Today, ' . $remarkDate->format('h:i A') : ($remarkDate->isYesterday() ? 'Yesterday, ' . $remarkDate->format('h:i A') : $remarkDate->format('d M, h:i A'))) : '';
            @endphp
            <div class="mt-1" style="font-size:11px; color:#64748b; line-height:1.3;" title="Latest Remark by {{ $remarkUser }} on {{ $remarkDate ? $remarkDate->format('d M Y, h:i A') : '' }}: {{ $task->latest_remark_text }}">
                <span class="text-primary" style="font-weight:600;"><i class="fa fa-comment-dots me-1"></i>{{ $remarkUser }}:</span>
                <span style="color:#475569;">{{ \Illuminate\Support\Str::limit($task->latest_remark_text, 35) }}</span>
                @if($humanTime)
                    <span class="text-muted" style="font-size:10px;">({{ $humanTime }})</span>
                @endif
            </div>
        @endif
    </td>

    <td style="width:110px;">
        <span class="badge" style="background:#eef2ff; color:#4338ca; font-weight:500; padding:5px 8px; border-radius:6px; font-size:11px;">
            {{ ucfirst($task->task_type) }}
        </span>
    </td>

    @php
        $currentSubtask = $task->subtask_id ? ($subtasks[$task->subtask_id] ?? '') : '';
    @endphp
    <td style="min-width:150px;">
        @if($isAdmin || hasPermission('task_change_subtask_status'))
        <select class="form-select form-select-sm changeSubtask shadow-sm" data-id="{{ $task->id }}" style="min-width:140px; background-color:#f8fafc; color:#334155; font-weight:500; border:1px solid #cbd5e1; border-radius:6px; cursor:pointer; padding:4px 8px; font-size:12px;">
            <option value="" style="color:#94a3b8;">- Select Subtask -</option>
            @foreach($subtasks as $id => $name)
                <option value="{{ $id }}" {{ $task->subtask_id == $id ? 'selected' : '' }}>
                    {{ $name }}
                </option>
            @endforeach
        </select>
        @else
            <span style="color:#475569; font-weight:500;">{{ $currentSubtask ?: '-' }}</span>
        @endif
    </td>

    <td style="min-width:120px; color:#475569;">{{ $task->location->name ?? '-' }}</td>

    <td style="min-width:120px; color:#475569;">{{ $task->group->group_name ?? '-' }}</td>

    <td style="color:#64748b; font-size:12px; white-space:nowrap;">{{ $task->from_date ?: '-' }}</td>

    <td style="color:#64748b; font-size:12px; white-space:nowrap;">{{ $task->to_date ?: '-' }}</td>

    <!-- Assigned -->
    @php
        $assignedNames = $task->taskUsers
            ->map(function ($assign) {
                return $assign->employee->user->name ?? null;
            })
            ->filter()
            ->implode(', ');
        $count = $task->taskUsers->count();
    @endphp
    <td style="min-width:110px;">
        <div class="d-flex align-items-center" title="{{ $assignedNames ?: 'None' }}">
            @forelse($task->taskUsers->take(3) as $assign)
                <img src="{{ !empty($assign->employee->image) 
                ? asset('uploads/employees/'.$assign->employee->image) 
                : asset('uploads/employees/user.png') }}"
                 title="{{ $assign->employee->user->name ?? 'Unknown' }}"
                 style="width:30px;height:30px;border-radius:50%;object-fit:cover;border:2px solid #fff;margin-left:-6px;box-shadow:0 1px 3px rgba(0,0,0,0.12);">
            @empty
                <span class="text-muted" style="font-size:12px;">-</span>
            @endforelse

            @if($count > 3)
                <div style="width:30px;height:30px;border-radius:50%;background:#0d6efd;color:#fff;font-size:10px;font-weight:600;display:flex;align-items:center;justify-content:center;margin-left:-6px;border:2px solid #fff;box-shadow:0 1px 3px rgba(0,0,0,0.12);" title="{{ $assignedNames }}">
                    +{{ $count - 3 }}
                </div>
            @endif
        </div>
    </td>

    <td style="min-width:110px; color:#475569;">{{ $task->createdBy->name ?? '-' }}</td>

    <!-- ACTION -->
    <td style="white-space:nowrap; min-width:180px;">
        <div class="d-flex align-items-center gap-1">
            @if($isAdmin || hasPermission('task_view_all') || hasPermission('task_view_own'))
            <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-light btn-sm border" title="View Details" style="padding:4px 8px; border-radius:6px;">
                <i class="fa fa-eye text-info"></i>
            </a>
            @endif

            @if($isAdmin || hasPermission('task_edit') || hasPermission('task_view_all') || hasPermission('task_view_own'))
            <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-light btn-sm border" title="Edit Task" style="padding:4px 8px; border-radius:6px;">
                <i class="fa fa-edit text-warning"></i>
            </a>
            @endif

            @if($isAdmin || hasPermission('task_delete') || hasPermission('task_edit') || hasPermission('task_view_all') || hasPermission('task_view_own'))
            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="delete-form d-inline">
                @csrf @method('DELETE')
                <button type="button" class="btn btn-light btn-sm border delete-btn" title="Delete Task" style="padding:4px 8px; border-radius:6px;">
                    <i class="fa fa-trash text-danger"></i>
                </button>
            </form>
            @endif

            <button type="button"
                class="btn btn-outline-primary btn-sm remarkBtn"
                data-id="{{ $task->id }}"
                data-title="{{ $task->title }}"
                style="padding:4px 10px; border-radius:6px; font-weight:500; font-size:12px;"
                title="Add Remark">
                <i class="fa fa-commenting me-1"></i> Remark
            </button>
        </div>
    </td>

    <!-- STATUS -->
    <td style="min-width:145px;">
        @if($isAdmin || hasPermission('task_change_status'))
        <select class="form-select form-select-sm changeStatus shadow-sm"
                data-id="{{ $task->id }}"
                style="border-radius:6px; font-size:12px; font-weight:500; padding:4px 8px; cursor:pointer; border:1px solid #cbd5e1; background-color:#fff;">
            <option value="pending" {{ $task->status=='pending' ? 'selected' : '' }}>Pending</option>
            <option value="in_progress" {{ $task->status=='in_progress' ? 'selected' : '' }}>In Progress</option>
            <option value="completed" {{ $task->status=='completed' ? 'selected' : '' }}>Completed</option>
        </select>
        @else
            @if($task->status == 'pending')
                <span class="task-status-badge task-status-pending">Pending</span>
            @elseif($task->status == 'in_progress')
                <span class="task-status-badge task-status-progress">In Progress</span>
            @else
                <span class="task-status-badge task-status-completed">Completed</span>
            @endif
        @endif
    </td>

</tr>
@empty
<tr>
    <td colspan="12" class="text-center py-5 text-muted">
        <div class="py-3">
            <i class="fa fa-folder-open-o fa-3x mb-2 text-secondary" style="opacity:0.5;"></i>
            <h6 class="font-weight-bold text-secondary mb-1">No tasks found</h6>
            <p class="text-muted small mb-0">Try adjusting your filters or search keywords.</p>
        </div>
    </td>
</tr>
@endforelse