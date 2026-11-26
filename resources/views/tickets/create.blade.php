@extends('layouts.master')

@section('title', 'File Complaint')

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
    background: linear-gradient(135deg, #10b981, #059669);
    border: none;
    color: #fff;
    padding: 10px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
    transition: all 0.2s ease-in-out;
}

.btn-submit:hover {
    background: linear-gradient(135deg, #059669, #047857);
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
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
@endphp

{{-- 🔐 PERMISSION CHECK --}}
@if($isAdmin || hasPermission('ticket_add'))

<x-breadcrumb title="Employee Complaints" page="File Complaint" />

<div class="row">
<div class="col-md-12">

<div class="main-card">

<form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data">
@csrf

<div class="row">

    {{-- TITLE --}}
    <div class="col-md-4 field-box">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" name="title"
               class="form-control"
               value="{{ old('title') }}" placeholder="Enter complaint title">

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
                    {{ old('department_id') == $dept->id ? 'selected' : '' }}>
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
            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
            <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
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
            <option value="individual" {{ old('ticket_type') == 'individual' ? 'selected' : '' }}>Individual</option>
            <option value="location" {{ old('ticket_type') == 'location' ? 'selected' : '' }}>Location</option>
        </select>

        @error('ticket_type')
            <div class="error-text">{{ $message }}</div>
        @enderror
    </div>

    {{-- COMPLAINED EMPLOYEE(S) (FOR INDIVIDUAL) --}}
    <div class="col-md-4 field-box d-none" id="employeeBox">
        <label class="form-label">Complained Employee(s) <span class="text-danger">*</span></label>
        <select name="assigned_to[]" id="assigned_to" class="form-control" multiple>
            @foreach($employees as $emp)
                <option value="{{ $emp->id }}"
                    {{ (is_array(old('assigned_to')) && in_array($emp->id, old('assigned_to'))) ? 'selected' : '' }}>
                    {{ $emp->user->name ?? $emp->employee_name }}
                </option>
            @endforeach
        </select>

        @error('assigned_to')
            <div class="error-text">{{ $message }}</div>
        @enderror
    </div>

    {{-- LOCATION (FOR LOCATION) --}}
    <div class="col-md-4 field-box d-none" id="locationBox">
        <label class="form-label">Location <span class="text-danger">*</span></label>
        <select name="location_id" id="location_id" class="form-control">
            <option value="">Select Location</option>
            @foreach($locations as $loc)
                <option value="{{ $loc->id }}"
                    {{ old('location_id') == $loc->id ? 'selected' : '' }}>
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
        <input type="text" name="subject"
               class="form-control"
               value="{{ old('subject') }}" placeholder="Brief subject of complaint">

        @error('subject')
            <div class="error-text">{{ $message }}</div>
        @enderror
    </div>

    {{-- ATTACHMENT --}}
    <div class="col-md-4 field-box">
        <label class="form-label">Attachment (Optional)</label>
        <input type="file" name="attachment" id="ticketAttachmentInput" class="form-control" style="padding: 7px 10px;" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip">
        <small class="text-muted d-block mt-1">Allowed: jpg, png, pdf, doc, xls, zip (Max 15MB)</small>

        {{-- LIVE PREVIEW OF SELECTED FILE --}}
        <div id="attachmentLivePreview" class="mt-2" style="display:none;">
            <div class="d-flex align-items-center p-2 rounded border bg-light" style="gap:10px;">
                <div id="previewThumbnailBox" style="width:48px; height:48px; border-radius:6px; overflow:hidden; display:flex; align-items:center; justify-content:center; background:#fff; border:1px solid #ddd; font-size:22px; flex-shrink:0;"></div>
                <div style="min-width:0; flex:1;">
                    <div id="previewNameLabel" class="font-weight-bold text-truncate" style="font-size:12px; color:#1e293b;"></div>
                    <small id="previewSizeLabel" class="text-muted" style="font-size:11px;"></small>
                    <div class="mt-1">
                        <a href="javascript:void(0)" id="previewViewLink" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:11px; display:none;">
                            <i class="fa fa-eye mr-1"></i> View / Preview
                        </a>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-link text-danger p-0" id="clearAttachmentBtn" title="Remove file" style="font-size:16px;">
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
        <textarea name="description" class="form-control" rows="4" placeholder="Provide detailed explanation of the issue / complaint...">{{ old('description') }}</textarea>

        @error('description')
            <div class="error-text">{{ $message }}</div>
        @enderror
    </div>

</div>

<div class="mt-2 d-flex align-items-center">
    <button type="submit" class="btn btn-submit">
        <i class="fa fa-paper-plane me-1"></i> File Complaint
    </button>
    <a href="{{ route('tickets.index') }}" class="btn btn-cancel ms-2">
        <i class="fa fa-times me-1"></i> Cancel
    </a>
</div>

</form>

</div>
</div>
</div>

@else

<div class="alert alert-danger">
    You do not have permission to file complaint.
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
            $('#location_id').val('');
        } else if (type === 'location') {
            $('#locationBox').removeClass('d-none');
            $('#employeeBox').addClass('d-none');
            if (employeeChoices) {
                employeeChoices.removeActiveItems();
            }
        } else {
            $('#employeeBox').addClass('d-none');
            $('#locationBox').addClass('d-none');
            $('#location_id').val('');
            if (employeeChoices) {
                employeeChoices.removeActiveItems();
            }
        }
    }

    checkTicketType();

    $('#ticket_type').on('change', function(){
        checkTicketType();
    });

    /* ================= ATTACHMENT PREVIEW ================= */
    let createdObjectUrl = null;

    $('#ticketAttachmentInput').on('change', function(){
        const file = this.files[0];
        if(!file){
            $('#attachmentLivePreview').hide();
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

        $('#previewNameLabel').text(file.name);
        $('#previewSizeLabel').text(sizeText);

        if(createdObjectUrl){
            URL.revokeObjectURL(createdObjectUrl);
            createdObjectUrl = null;
        }

        createdObjectUrl = URL.createObjectURL(file);
        $('#previewViewLink').attr('href', createdObjectUrl).show();

        if(isImg){
            $('#previewThumbnailBox').html(`
                <img src="${createdObjectUrl}" style="width:100%; height:100%; object-fit:cover;">
            `);
        } else {
            let icon = 'fa-file-lines text-secondary';
            if(ext === 'pdf') icon = 'fa-file-pdf text-danger';
            else if(['doc','docx'].includes(ext)) icon = 'fa-file-word text-primary';
            else if(['xls','xlsx','csv'].includes(ext)) icon = 'fa-file-excel text-success';
            else if(['zip','rar'].includes(ext)) icon = 'fa-file-zipper text-warning';

            $('#previewThumbnailBox').html(`<i class="fa ${icon}"></i>`);
        }

        $('#attachmentLivePreview').slideDown(150);
    });

    $('#clearAttachmentBtn').on('click', function(){
        $('#ticketAttachmentInput').val('');
        if(createdObjectUrl){
            URL.revokeObjectURL(createdObjectUrl);
            createdObjectUrl = null;
        }
        $('#attachmentLivePreview').slideUp(150);
    });

});
</script>
@endsection
