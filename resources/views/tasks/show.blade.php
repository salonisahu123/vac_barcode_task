@extends('layouts.master')

@section('title','Task Details')

@section('css')
<!-- ADD -->
<link rel="stylesheet" href="{{ asset('css/task.css') }}">
@endsection

@section('content')

@if(auth()->user()->user_type == 'admin' || hasPermission('task_view_all') || hasPermission('task_view_own'))

<x-breadcrumb title="Task Management" page="Task Details" />

<div class="container-fluid mt-3">

<div class="task-card">

    {{-- HEADER --}}
   {{-- HEADER --}}
<div class="task-header d-flex justify-content-between align-items-center">

    {{-- LEFT SIDE --}}
    <div>
        <div class="task-title">
            <i class="fa fa-tasks text-primary"></i>
            {{ $task['title'] }}
        </div>
        <small class="text-muted">
            Created By {{ $task['created_by'] }}
        </small>
    </div>

    {{-- RIGHT SIDE ACTION BUTTONS --}}
    <div class="d-flex gap-2">

        {{-- BACK BUTTON --}}
        <a href="{{ route('tasks.index') }}"
           class="btn btn-light btn-sm shadow-sm"
           style="border-radius:8px;">
            <i class="fa fa-arrow-left"></i> Back
        </a>

        {{-- EDIT BUTTON --}}
        @if(auth()->user()->user_type == 'admin' || hasPermission('task_edit'))
            <a href="{{ route('tasks.edit', $task['id'] ?? $task->id) }}"
               class="btn btn-primary btn-sm shadow-sm"
               style="border-radius:8px;">
                <i class="fa fa-edit"></i> Edit
            </a>
        @endif

    </div>

</div>

    {{-- TABS --}}
    <div class="tab-box">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#overview">Overview</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#activity"><i class="fa fa-comment-dots mr-1"></i> Remarks ({{ count($activities) }})</a>
            </li>
        </ul>
    </div>

    <div class="tab-content p-3">

        {{-- OVERVIEW --}}

<div class="tab-pane fade show active" id="overview">

    @php
        $statusClass = [
            'pending' => 'warning',
            'in_progress' => 'info',
            'completed' => 'success'
        ];
    @endphp

    <div class="card border-0 shadow-sm rounded-lg" style="overflow:hidden;">
        
        {{-- Card Header for Title & Description --}}
        <div class="card-body p-4 border-bottom" style="background: #fdfdfe;">
            <div class="row">
                <div class="col-md-9">
                    <h6 class="text-uppercase text-muted mb-1" style="font-size:11px; font-weight:700; letter-spacing:0.8px;">Task Overview</h6>
                    <h3 class="mb-3" style="color: #2b3445; font-weight: 700; font-size:24px;">
                        {{ $task->title ?? 'No Title' }}
                    </h3>
                    <p class="text-secondary mb-0" style="font-size:14px; line-height: 1.7;">
                        {{ $task->description ?? 'No Description Available' }}
                    </p>
                </div>
                <div class="col-md-3 text-md-right mt-3 mt-md-0">
                    <div class="mb-2">
                        <small class="text-muted d-block text-uppercase" style="font-size:11px; font-weight:700; letter-spacing:0.5px;">Status</small>
                        <span class="badge badge-{{ $statusClass[$task->status] ?? 'secondary' }} px-3 py-2 mt-1" style="font-size:13px; font-weight:600; border-radius:6px;">
                            {{ ucfirst(str_replace('_', ' ', $task->status ?? 'Not Available')) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Meta Information Grid --}}
        <div class="card-body p-4 bg-white border-bottom">
            <h6 class="text-uppercase text-muted mb-3" style="font-size:11px; font-weight:700; letter-spacing:0.8px;">Task Details</h6>
            <div class="row">
                <div class="col-md-3 col-6 mb-3">
                    <small class="text-muted d-block mb-1">Task Type</small>
                    <div class="text-dark" style="font-size:14px; font-weight:600;">
                        <i class="fa fa-tag text-primary mr-1"></i> {{ ucfirst($task->task_type ?? 'Not Available') }}
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <small class="text-muted d-block mb-1">Sub Task</small>
                    <div class="text-dark" style="font-size:14px; font-weight:600;">
                        <i class="fa fa-tasks text-success mr-1"></i> {{ $task->subtask_name ?? '-' }}
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <small class="text-muted d-block mb-1">Mode</small>
                    <div class="text-dark" style="font-size:14px; font-weight:600;">
                        @if(!empty($task->mode))
                            <i class="fa fa-sliders-h text-secondary mr-1"></i> {{ ucfirst($task->mode) }}
                        @else
                            <span class="text-danger"><i class="fa fa-times-circle mr-1"></i> No Mode</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <small class="text-muted d-block mb-1">Timeline</small>
                    <div class="text-dark" style="font-size:14px; font-weight:600;">
                        <i class="fa fa-calendar-alt text-warning mr-1"></i> 
                        {{ \Carbon\Carbon::parse($task->from_date)->format('d M y') }} 
                        <i class="fa fa-arrow-right mx-1 text-muted" style="font-size:10px;"></i> 
                        {{ \Carbon\Carbon::parse($task->to_date)->format('d M y') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Assignees and Files --}}
        <div class="card-body p-4 bg-white">
            <div class="row">
                
                {{-- Assignees --}}
                <div class="col-md-8 mb-4 mb-md-0">
                    <h6 class="text-uppercase text-muted mb-3" style="font-size:11px; font-weight:700; letter-spacing:0.8px;">Assigned Team</h6>
                    <div class="d-flex flex-wrap" style="gap:12px;">
                        @forelse($task->taskUsers as $assign)
                            @php $emp = $assign->employee->user ?? null; @endphp
                            <div class="d-flex align-items-center rounded px-3 py-2 border" style="background:#f8f9fa; border-color: #e9ecef !important; min-width: 180px;">
                                <img src="{{ !empty($assign->employee->image) ? asset('uploads/employees/'.$assign->employee->image) : asset('uploads/employees/user.png') }}" class="rounded-circle mr-2 shadow-sm" width="36" height="36" style="object-fit:cover; border:2px solid #fff;">
                                <div>
                                    <div style="font-size:13px; font-weight:600; color:#333;">{{ $emp->name ?? 'Employee' }}</div>
                                    <div style="font-size:11px; color:#888;">Assignee</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted p-2" style="font-size:14px;">No Employees Assigned</div>
                        @endforelse
                    </div>
                </div>

                {{-- Attachments --}}
                <div class="col-md-4 border-left">
                    <h6 class="text-uppercase text-muted mb-3" style="font-size:11px; font-weight:700; letter-spacing:0.8px;">Attachments</h6>
                    @if(!empty($task->file))
                        <div class="d-flex align-items-center p-3 border rounded" style="background:#f8f9fa;">
                            <div class="mr-3 text-danger" style="font-size:28px;">
                                <i class="fa fa-file-alt"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div style="font-size:13px; font-weight:600;" class="text-dark mb-1">Attached Document</div>
                                <a href="{{ asset('uploads/tasks/'.$task->file) }}" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size:11px; font-weight:600;">
                                    View File <i class="fa fa-external-link-alt ml-1"></i>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="d-flex align-items-center justify-content-center p-3 rounded" style="background:#fafafa; border: 1px dashed #ced4da;">
                            <div class="text-muted text-center w-100" style="font-size:13px;">
                                <i class="fa fa-folder-open mb-2" style="font-size:22px; opacity:0.4;"></i><br>
                                No attachments
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        </div>
        
    </div>

</div>

{{-- ACTIVITY (FULL HISTORY) --}}
    

<div class="tab-pane fade" id="activity">

    <div class="activity-wrapper">

        {{-- HEADER --}}
        <div class="activity-header d-flex justify-content-between align-items-center mb-3">
            <div>
                <i class="fa fa-comments text-primary mr-1"></i>
                <span class="font-weight-bold">Remarks Timeline</span>
            </div>
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#remarkModal">
                <i class="fa fa-plus mr-1"></i> Add Remark
            </button>
        </div>

        {{-- TIMELINE --}}
        <div class="timeline">

            @forelse($activities as $act)

                @php
                    $userName = \App\Models\User::find($act->user_id)?->name;
                    $remarkText = $act->description;
                    if (preg_match('/^Remark added on task ".*":\s*(.*)$/s', $remarkText, $matches)) {
                        $remarkText = $matches[1];
                    } elseif (preg_match('/^Task ".*" subtask changed from (.*)$/s', $remarkText, $matches)) {
                        $remarkText = 'Subtask changed from ' . $matches[1];
                    } elseif (preg_match('/^Task ".*" status changed from (.*) to (.*)$/s', $remarkText, $matches)) {
                        $from = ucfirst(str_replace('_', ' ', trim($matches[1])));
                        $to   = ucfirst(str_replace('_', ' ', trim($matches[2])));
                        $remarkText = 'Status changed from ' . $from . ' to ' . $to;
                    } elseif (str_contains($remarkText, '": ')) {
                        $remarkText = \Illuminate\Support\Str::after($remarkText, '": ');
                    }
                    $isSubtaskChange = str_starts_with($remarkText, 'Subtask changed') || $act->operation === 'subtask_change';
                    $isStatusChange  = str_starts_with($remarkText, 'Status changed')  || $act->operation === 'status_change';
                    $themeColor = $isSubtaskChange ? '#10b981' : ($isStatusChange ? '#f59e0b' : '#2563eb');
                @endphp

                <div class="timeline-item">

                    {{-- DOT --}}
                    <div class="timeline-dot" style="background:{{ $themeColor }};"></div>

                    {{-- CONTENT --}}
                    <div class="timeline-content" style="background:#fff; border: 1px solid #e9ecef; border-left: 3px solid {{ $themeColor }}; border-radius: 8px; padding: 12px 16px; margin-bottom: 12px;">

                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="timeline-user" style="font-weight:600; color:#1e293b; font-size:13px;">
                                <i class="fa fa-user-circle mr-1" style="color:{{ $themeColor }};"></i> {{ $userName ?? 'User' }}
                                @if($isSubtaskChange)
                                    <span class="badge badge-light border text-success ml-2" style="font-size:10px; font-weight:600; vertical-align:middle;">
                                        <i class="fa fa-tasks mr-1"></i> Subtask Update
                                    </span>
                                @elseif($isStatusChange)
                                    <span class="badge badge-light border text-warning ml-2" style="font-size:10px; font-weight:600; vertical-align:middle;">
                                        <i class="fa fa-info-circle mr-1"></i> Status Update
                                    </span>
                                @else
                                    <span class="badge badge-light border text-primary ml-2" style="font-size:10px; font-weight:600; vertical-align:middle;">
                                        <i class="fa fa-comment-dots mr-1"></i> Note / Remark
                                    </span>
                                @endif
                            </div>

                            <div class="timeline-time text-muted" style="font-size:12px;">
                                <i class="fa fa-clock mr-1"></i>
                                {{ \Carbon\Carbon::parse($act->created_at)->format('d M Y h:i A') }}
                            </div>
                        </div>

                        <div class="timeline-desc mt-2" style="font-size:14px; color:#334155; line-height:1.6; white-space:pre-line;">
                            {{ $remarkText }}
                        </div>

                    </div>

                </div>

            @empty
                <div class="text-muted p-4 text-center bg-white rounded border">
                    <i class="fa fa-comment-slash fa-2x mb-2 d-block text-secondary" style="opacity: 0.4;"></i>
                    No remarks found on this task.
                </div>
            @endforelse

        </div>

    </div>

</div>

    </div>

</div>

</div>

{{-- ADD REMARK MODAL --}}
<div class="modal fade" id="remarkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-comment-dots text-primary mr-1"></i> Add Remark</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <label class="font-weight-bold">Remark <span class="text-danger">*</span></label>
                <textarea id="task_show_remark_text" class="form-control" rows="4" placeholder="Enter remark..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnSubmitShowRemark">Submit</button>
            </div>
        </div>
    </div>
</div>

@else
<div class="alert alert-danger">No Permission</div>
@endif

@endsection

@section('script')
<script>
$(document).ready(function () {
    $('#btnSubmitShowRemark').on('click', function () {
        let remark = $('#task_show_remark_text').val().trim();
        if (!remark) {
            Swal.fire('Error', 'Please enter remark', 'error');
            return;
        }
        let $btn = $(this);
        $btn.prop('disabled', true).text('Submitting...');

        $.ajax({
            url: '/tasks/{{ $task->id }}/remark',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                remark: remark
            },
            success: function (res) {
                $('#remarkModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: res.message || 'Remark added successfully'
                }).then(() => {
                    location.reload();
                });
            },
            error: function (xhr) {
                $btn.prop('disabled', false).text('Submit');
                Swal.fire('Error', xhr.responseJSON?.message || 'Server error', 'error');
            }
        });
    });
});
</script>


@endsection