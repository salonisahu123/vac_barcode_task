@extends('layouts.master')

@section('title', 'Edit Complaint')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<link rel="stylesheet" href="{{ asset('css/ticket.css') }}">
<style>
/* Main Card & Typography */
.main-card {
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
    border: 1px solid #eef1f6;
    padding: 24px !important;
}

.field-box {
    margin-bottom: 20px;
}

.form-label {
    font-size: 13px;
    font-weight: 600;
    color: #344054;
    margin-bottom: 6px;
    display: block;
}

/* Inputs & Selects */
.form-control {
    border: 1px solid #dfe3ea !important;
    border-radius: 10px !important;
    font-size: 13px !important;
    min-height: 44px;
    color: #1f2937;
    background-color: #fff;
    box-shadow: none !important;
}

.form-control:focus {
    border-color: #4c8bf5 !important;
    box-shadow: 0 0 0 0.15rem rgba(76, 139, 245, 0.15) !important;
}

select.form-control:not([size]):not([multiple]) {
    height: 44px !important;
    padding: 8px 12px;
}

/* Choices.js Custom Theme */
.choices {
    margin-bottom: 0;
    font-size: 13px;
}

.choices__inner {
    min-height: 44px !important;
    border: 1px solid #dfe3ea !important;
    border-radius: 10px !important;
    background-color: #fff !important;
    padding: 4px 10px !important;
    font-size: 13px !important;
    box-shadow: none !important;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 4px;
}

.choices.is-focused .choices__inner {
    border-color: #4c8bf5 !important;
    box-shadow: 0 0 0 0.15rem rgba(76, 139, 245, 0.15) !important;
}

.choices__list--multiple .choices__item {
    background: linear-gradient(135deg, #0d6efd, #0b57d0) !important;
    border: none !important;
    border-radius: 6px !important;
    color: #fff !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    padding: 3px 8px !important;
    margin: 2px !important;
    display: inline-flex;
    align-items: center;
}

.choices__list--multiple .choices__item .choices__button {
    border-left: 1px solid rgba(255, 255, 255, 0.3) !important;
    margin-left: 6px !important;
    padding-left: 4px !important;
    opacity: 0.8 !important;
    filter: invert(1);
}

.choices__list--multiple .choices__item .choices__button:hover {
    opacity: 1 !important;
}

.choices__list--dropdown {
    border: 1px solid #dfe3ea !important;
    border-radius: 10px !important;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12) !important;
    margin-top: 4px !important;
    z-index: 1050 !important;
    background: #fff !important;
}

.choices__list--dropdown .choices__item--selectable {
    padding: 9px 14px !important;
    font-size: 13px !important;
}

.choices__list--dropdown .choices__item--selectable.is-highlighted {
    background-color: #f0f4ff !important;
    color: #0b57d0 !important;
}

/* Action Buttons */
.btn-submit {
    background: linear-gradient(135deg, #0d6efd, #0b57d0);
    border: none;
    color: #fff;
    padding: 10px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25);
    transition: all 0.2s ease-in-out;
}

.btn-submit:hover {
    background: linear-gradient(135deg, #0b57d0, #084298);
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(13, 110, 253, 0.35);
}

.btn-cancel {
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    color: #4b5563;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.2s ease-in-out;
}

.btn-cancel:hover {
    background: #e5e7eb;
    color: #1f2937;
}

.error-text {
    font-size: 12px;
    color: #dc2626;
    margin-top: 4px;
}
</style>
@endsection

@section('content')

@php
$user = Auth::user();
$isAdmin = $user->user_type == 'admin';
$canEdit = $isAdmin || hasPermission('ticket_edit') || ($ticket->user_id == Auth::id() && (hasPermission('ticket_view_own') || hasPermission('ticket_add')));
@endphp

{{-- 🔐 PERMISSION CHECK --}}
@if($canEdit)

<x-breadcrumb title="Employee Complaints" page="Edit Complaint" />

<div class="row">
<div class="col-md-12">

<div class="main-card">

<form method="POST" action="{{ route('tickets.update',$ticket->id) }}" enctype="multipart/form-data">
@csrf
@method('PUT')

<div class="row">

    {{-- TITLE --}}
    <div class="col-md-4 field-box">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" name="title"
               class="form-control"
               value="{{ old('title',$ticket->title) }}" placeholder="Enter complaint title">

        @error('title')
        <div class="error-text">{{ $message }}</div>
        @enderror
    </div>

    {{-- DEPARTMENT --}}
    <div class="col-md-4 field-box">
        <label class="form-label">Department <span class="text-danger">*</span></label>
        <select name="department_id" class="form-control">
            <option value="">Select Department</option>
            @foreach($departments as $dept)
            <option value="{{ $dept->id }}"
                {{ old('department_id',$ticket->department_id) == $dept->id ? 'selected' : '' }}>
                {{ $dept->dept_name }}
            </option>
            @endforeach
        </select>

        @error('department_id')
        <div class="error-text">{{ $message }}</div>
        @enderror
    </div>

    {{-- PRIORITY --}}
    <div class="col-md-4 field-box">
        <label class="form-label">Priority <span class="text-danger">*</span></label>
        <select name="priority" class="form-control">
            <option value="low" {{ old('priority',$ticket->priority) == 'low' ? 'selected' : '' }}>Low</option>
            <option value="medium" {{ old('priority',$ticket->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
            <option value="high" {{ old('priority',$ticket->priority) == 'high' ? 'selected' : '' }}>High</option>
        </select>

        @error('priority')
        <div class="error-text">{{ $message }}</div>
        @enderror
    </div>

    {{-- COMPLAINT TYPE --}}
    <div class="col-md-4 field-box">
        <label class="form-label">Complaint Type <span class="text-danger">*</span></label>
        <select name="ticket_type" id="ticket_type" class="form-control">
            <option value="">Select Complaint Type</option>
            <option value="individual"
                {{ old('ticket_type',$ticket->ticket_type) == 'individual' ? 'selected' : '' }}>
                Individual
            </option>
            <option value="location"
                {{ old('ticket_type',$ticket->ticket_type) == 'location' ? 'selected' : '' }}>
                Location
            </option>
        </select>

        @error('ticket_type')
        <div class="error-text">{{ $message }}</div>
        @enderror
    </div>

    {{-- COMPLAINED EMPLOYEE(S) FOR INDIVIDUAL --}}
    @php
        $assignedEmployeeIds = $ticket->assignments->pluck('assigned_to')->toArray();
    @endphp
    <div class="col-md-4 field-box d-none" id="employeeBox">
        <label class="form-label">Complained Employee(s) <span class="text-danger">*</span></label>
        <select name="assigned_to[]" id="assigned_to" class="form-control" multiple>
            @foreach($employees as $emp)
                <option value="{{ $emp->id }}"
                    {{ (is_array(old('assigned_to')) ? in_array($emp->id, old('assigned_to')) : in_array($emp->id, $assignedEmployeeIds)) ? 'selected' : '' }}>
                    {{ $emp->user->name ?? $emp->employee_name }}
                </option>
            @endforeach
        </select>
        @error('assigned_to')
        <div class="error-text">{{ $message }}</div>
        @enderror
    </div>

    {{-- LOCATION --}}
    <div class="col-md-4 field-box d-none" id="locationBox">
        <label class="form-label">Location <span class="text-danger">*</span></label>
        <select name="location_id" id="location_id" class="form-control">
            <option value="">Select Location</option>
            @foreach($locations as $loc)
            <option value="{{ $loc->id }}"
                {{ old('location_id',$ticket->location_id) == $loc->id ? 'selected' : '' }}>
                {{ $loc->name }}
            </option>
            @endforeach
        </select>

        @error('location_id')
        <div class="error-text">{{ $message }}</div>
        @enderror
    </div>

    {{-- SUBJECT --}}
    <div class="col-md-4 field-box">
        <label class="form-label">Subject <span class="text-danger">*</span></label>
        <input type="text"
               name="subject"
               class="form-control"
               value="{{ old('subject',$ticket->subject) }}" placeholder="Brief subject of complaint">

        @error('subject')
        <div class="error-text">{{ $message }}</div>
        @enderror
    </div>

    {{-- ATTACHMENT --}}
    <div class="col-md-4 field-box">
        <label class="form-label">Attachment (Optional)</label>
        <input type="file" name="attachment" id="ticketAttachmentInputEdit" class="form-control" style="padding: 7px 10px;" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip">

        @if($ticket->attachment)
        @php
            $editExt = strtolower(pathinfo($ticket->attachment, PATHINFO_EXTENSION));
            $isEditImg = in_array($editExt, ['jpg','jpeg','png','gif','webp']);
            $editUrl = asset('uploads/tickets/'.$ticket->attachment);
        @endphp
        <div class="mt-2 p-2 rounded border bg-light d-flex align-items-center" style="gap:8px;">
            <div style="font-size:18px;">
                @if($isEditImg)
                    <i class="fa fa-file-image text-info"></i>
                @elseif($editExt == 'pdf')
                    <i class="fa fa-file-pdf text-danger"></i>
                @else
                    <i class="fa fa-file text-secondary"></i>
                @endif
            </div>
            <div style="min-width:0; flex:1;">
                <small class="text-muted d-block" style="font-size:10.5px;">Current Attachment:</small>
                <a href="{{ $editUrl }}" target="_blank" class="font-weight-bold text-truncate d-block" style="font-size:12px;">
                    <i class="fa fa-external-link-alt mr-1"></i> {{ preg_replace('/^\d+_/', '', $ticket->attachment) }}
                </a>
            </div>
            <a href="{{ $editUrl }}" download="{{ preg_replace('/^\d+_/', '', $ticket->attachment) }}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:11px;">
                <i class="fa fa-download"></i>
            </a>
        </div>
        @endif

        {{-- NEW FILE PREVIEW --}}
        <div id="attachmentLivePreviewEdit" class="mt-2" style="display:none;">
            <div class="d-flex align-items-center p-2 rounded border bg-white" style="gap:10px;">
                <div id="previewThumbnailBoxEdit" style="width:40px; height:40px; border-radius:6px; overflow:hidden; display:flex; align-items:center; justify-content:center; background:#f8fafc; border:1px solid #ddd; font-size:18px; flex-shrink:0;"></div>
                <div style="min-width:0; flex:1;">
                    <div id="previewNameLabelEdit" class="font-weight-bold text-truncate" style="font-size:12px; color:#1e293b;"></div>
                    <small id="previewSizeLabelEdit" class="text-muted" style="font-size:11px;"></small>
                </div>
                <button type="button" class="btn btn-sm btn-link text-danger p-0" id="clearAttachmentBtnEdit" title="Remove new file" style="font-size:16px;">
                    <i class="fa fa-times-circle"></i>
                </button>
            </div>
        </div>

        @error('attachment')
        <div class="error-text">{{ $message }}</div>
        @enderror
    </div>

    {{-- DESCRIPTION --}}
    <div class="col-md-12 field-box">
        <label class="form-label">Description <span class="text-danger">*</span></label>
        <textarea name="description"
                  class="form-control"
                  rows="4" placeholder="Provide detailed explanation of the issue / complaint...">{{ old('description',$ticket->description) }}</textarea>

        @error('description')
        <div class="error-text">{{ $message }}</div>
        @enderror
    </div>

</div>

<div class="mt-2 d-flex align-items-center">
    <button type="submit" class="btn btn-submit">
        <i class="fa fa-save me-1"></i> Update Complaint
    </button>

    <a href="{{ route('tickets.index') }}" class="btn btn-cancel ms-2">
        <i class="fa fa-times me-1"></i> Back
    </a>
</div>

</form>

</div>
</div>
</div>

@else

<div class="alert alert-danger">
    You do not have permission to edit complaint.
</div>

@endif

@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
let employeeChoices = null;

$(document).ready(function(){

    const empSelect = document.getElementById('assigned_to');
    if (empSelect) {
        employeeChoices = new Choices(empSelect, {
            removeItemButton: true,
            searchEnabled: true,
            shouldSort: false,
            placeholder: true,
            placeholderValue: 'Select Complained Employee(s)',
            itemSelectText: '',
            noResultsText: 'No employee found',
            noChoicesText: 'No employees available'
        });
    }

    function checkTicketType() {
        let type = $('#ticket_type').val();

        if (type === 'individual') {
            $('#employeeBox').removeClass('d-none');
            $('#locationBox').addClass('d-none');
        } else if (type === 'location') {
            $('#locationBox').removeClass('d-none');
            $('#employeeBox').addClass('d-none');
        } else {
            $('#employeeBox').addClass('d-none');
            $('#locationBox').addClass('d-none');
        }
    }

    checkTicketType();

    $('#ticket_type').on('change', function () {
        checkTicketType();
    });

    /* ================= ATTACHMENT PREVIEW EDIT ================= */
    $('#ticketAttachmentInputEdit').on('change', function(){
        const file = this.files[0];
        if(!file){
            $('#attachmentLivePreviewEdit').hide();
            return;
        }

        const ext = file.name.split('.').pop().toLowerCase();
        const isImg = ['jpg','jpeg','png','gif','webp','bmp','svg'].includes(ext);

        let sizeText = '';
        if(file.size < 1024 * 1024){
            sizeText = (file.size / 1024).toFixed(1) + ' KB';
        } else {
            sizeText = (file.size / (1024 * 1024)).toFixed(1) + ' MB';
        }

        $('#previewNameLabelEdit').text(file.name);
        $('#previewSizeLabelEdit').text(sizeText);

        if(isImg){
            const reader = new FileReader();
            reader.onload = function(e){
                $('#previewThumbnailBoxEdit').html(`
                    <img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover;">
                `);
            };
            reader.readAsDataURL(file);
        } else {
            let icon = 'fa-file-lines text-secondary';
            if(ext === 'pdf') icon = 'fa-file-pdf text-danger';
            else if(['doc','docx'].includes(ext)) icon = 'fa-file-word text-primary';
            else if(['xls','xlsx','csv'].includes(ext)) icon = 'fa-file-excel text-success';
            else if(['zip','rar'].includes(ext)) icon = 'fa-file-zipper text-warning';

            $('#previewThumbnailBoxEdit').html(`<i class="fa ${icon}"></i>`);
        }

        $('#attachmentLivePreviewEdit').slideDown(150);
    });

    $('#clearAttachmentBtnEdit').on('click', function(){
        $('#ticketAttachmentInputEdit').val('');
        $('#attachmentLivePreviewEdit').slideUp(150);
    });

});
</script>
@endsection
