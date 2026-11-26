@php
    $isAdmin = $isAdmin ?? (auth()->check() && (auth()->user()->user_type == 'admin' || hasPermission('ticket_view_all')));
@endphp

@forelse($tickets as $key => $ticket)

<tr style="font-size:13px;">

    <td>{{ $key+1 }}</td>

    <td>
        <b>{{ $ticket->title }}</b>
        @if($ticket->attachment)
            <i class="fa fa-paperclip text-primary ms-1" title="Has Attachment" style="font-size:12px;"></i>
        @endif
    </td>

    <td data-search="{{ ucfirst($ticket->ticket_type) }}" data-filter="{{ ucfirst($ticket->ticket_type) }}" data-order="{{ ucfirst($ticket->ticket_type) }}">
        <span class="badge">
            {{ ucfirst($ticket->ticket_type) }}
        </span>
    </td>

    @php
        $canEditStatus = $ticket->can_edit_status ?? [];
        $statusText = ucfirst(str_replace('_',' ',$ticket->status));
    @endphp

    <td data-search="{{ $statusText }}" data-filter="{{ $statusText }}" data-order="{{ $statusText }}">

        <span class="status-badge status-{{ str_replace('_','-',$ticket->status) }}">
            {{ $statusText }}
        </span>

        <span style="display:none;" class="export-data">{{ $statusText }}</span>

        @if(
            ($canEditStatus['can_manage'] ?? false)
            || (($canEditStatus['created_by_me'] ?? false) && $ticket->status == 'close_request')
        )
            <button class="btn btn-sm btn-outline-primary changeStatusBtn ms-1"
                data-id="{{ $ticket->id }}"
                data-status="{{ $ticket->status }}">
                Change
            </button>
        @endif

    </td>

    <td>
        {{ $ticket->location->name ?? '-' }}
    </td>
    
    <td>
    @php
        $groupName = $ticket->assignments
            ->pluck('group.group_name')
            ->filter()
            ->unique()
            ->implode(', ');
    @endphp

    {{ $groupName ?: '-' }}
   </td>

    {{-- COMPLAINED USERS --}}
    @if($isAdmin)
    @php
        $assignedNames = $ticket->assignments->pluck('employee.user.name')->filter()->implode(', ');
    @endphp
    <td data-search="{{ $assignedNames }}" data-filter="{{ $assignedNames }}">
        <div class="d-flex align-items-center">
    
            @php $count = $ticket->assignments->count(); @endphp
    
            @forelse($ticket->assignments->take(3) as $assign)
                @if($assign->employee && $assign->employee->user)
    
                    <img src="{{ !empty($assign->employee->image) && file_exists(public_path('uploads/employees/'.$assign->employee->image)) 
                                ? asset('uploads/employees/'.$assign->employee->image) 
                                : asset('uploads/employees/user.png') }}"
                         
                         title="{{ $assign->employee->user->name }}"
                         style="width:32px;height:32px;border-radius:50%;object-fit:cover;margin-left:-6px;border:2px solid #fff;">
    
                @endif
            @empty
                <span class="text-muted">Not Specified</span>
            @endforelse
    
            @if($count > 3)
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                 style="width:32px;height:32px;font-size:11px;margin-left:-6px;">
                +{{ $count - 3 }}
            </div>
            @endif
    
        </div>
        <span style="display:none;" class="export-data">{{ $assignedNames ?: 'Not Specified' }}</span>
    </td>
    @endif

    {{-- ACTION --}}
    <td>
    <div class="d-flex gap-1 align-items-center">

        {{-- ASSIGN COMPLAINED EMPLOYEE BUTTON --}}
        @if(($isAdmin || hasPermission('ticket_assign') || hasPermission('ticket_change_status') || hasPermission('ticket_view_all')) && $ticket->status == 'pending')

        <button class="action-btn assignBtn"
            data-id="{{ $ticket->id }}"
            data-type="{{ $ticket->ticket_type }}"
            data-location="{{ $ticket->location->name ?? '' }}"
            data-locationid="{{ $ticket->location_id }}"
            title="Assign Complained Employee">

            <i class="fa fa-user-plus text-dark"></i>
        </button>

        @endif

        {{-- STATUS ACTION BUTTONS --}}
        @if($ticket->status == 'close_request' && $ticket->user_id == auth()->id())

            {{-- CLOSE --}}
            <button class="action-btn btn-close-ticket"
                data-id="{{ $ticket->id }}"
                title="Close Complaint">
                <i class="fa fa-check text-success"></i>
            </button>

            {{-- REOPEN --}}
            <button class="action-btn btn-reopen-ticket"
                data-id="{{ $ticket->id }}"
                title="Reopen Complaint">
                <i class="fa fa-undo text-warning"></i>
            </button>

        @endif

        {{-- VIEW --}}
        <a href="{{ route('tickets.show',$ticket->id) }}"
           class="action-btn"
           title="View Complaint">
            <i class="fa fa-eye text-info"></i>
        </a>
        
        {{-- EDIT --}}
        @php
            $canEdit = ($isAdmin || hasPermission('ticket_edit') || ($ticket->user_id == auth()->id() && (hasPermission('ticket_view_own') || hasPermission('ticket_add')))) 
                && in_array($ticket->status, ['pending', 'close_request']);
        @endphp
        @if($canEdit)
        <a href="{{ route('tickets.edit',$ticket->id) }}"
           class="action-btn"
           title="Edit Complaint">
            <i class="fa fa-edit text-warning"></i>
        </a>
        @endif

        {{-- DELETE --}}
        @if($isAdmin || hasPermission('ticket_delete'))
        <form action="{{ route('tickets.destroy',$ticket->id) }}"
              method="POST">
            @csrf
            @method('DELETE')

            <button type="button" class="action-btn delete-btn" title="Delete Complaint">
                <i class="fa fa-trash text-danger"></i>
            </button>
        </form>
        @endif

    </div>
</td>

</tr>

@empty
@endforelse