@extends('layouts.master')

@section('title','Complaint Details')

@section('css')
<style>

/* ================= PAGE ================= */
.page-wrap{
    background:#eef2f7;
    padding:15px;
}

/* ================= LAYOUT ================= */
.ticket-grid{
    display:flex;
    gap:18px;
}

.left-panel{
    width:58%;
}

.right-panel{
    width:42%;
    background:#fff;
    border-radius:14px;
    border:1px solid #e5e7eb;
    display:flex;
    flex-direction:column;
    height:86vh;
    overflow:hidden;
    box-shadow:0 6px 20px rgba(0,0,0,0.06);
}

@media(max-width:992px){
    .ticket-grid{
        flex-direction:column;
    }
    .left-panel, .right-panel{
        width:100%;
    }
    .right-panel{
        height:75vh;
    }
}

/* ================= CARD ================= */
.card-box{
    background:#fff;
    border-radius:12px;
    border:1px solid #e5e7eb;
    padding:16px;
    margin-bottom:14px;
    box-shadow:0 2px 6px rgba(0,0,0,0.03);
}

/* ================= BADGE ================= */
.badge-soft{
    font-size:11px;
    padding:4px 10px;
    border-radius:20px;
    font-weight:600;
}
.high{background:#fee2e2;color:#b91c1c;}
.medium{background:#fef3c7;color:#b45309;}
.low{background:#dbeafe;color:#1d4ed8;}

/* ================= CHAT HEADER ================= */
.chat-header{
    padding:14px 18px;
    background:linear-gradient(90deg, #128c7e, #075e54);
    color:#fff;
    font-weight:600;
    font-size:14px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.chat-header-left{
    display:flex;
    align-items:center;
    gap:8px;
}

.live-indicator{
    width:9px;
    height:9px;
    background:#25d366;
    border-radius:50%;
    box-shadow:0 0 0 2px rgba(255,255,255,0.4);
    display:inline-block;
    animation:pulse-live 2s infinite;
}

@keyframes pulse-live {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7); }
    70% { transform: scale(1.1); box-shadow: 0 0 0 6px rgba(37, 211, 102, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(37, 211, 102, 0); }
}

/* ================= CHAT BODY ================= */
.chat-body{
    flex:1;
    padding:14px;
    overflow-y:auto;
    background: #e5ddd5 url("https://i.pinimg.com/236x/79/3d/f7/793df797732aaf145a0f8a40d7e92cc1.jpg");
    background-size:280px;
}

/* SCROLLBAR */
.chat-body::-webkit-scrollbar{
    width:5px;
}
.chat-body::-webkit-scrollbar-thumb{
    background:#c0c4cc;
    border-radius:10px;
}

.msg-wrapper{
    display:flex;
    margin-bottom:10px;
    align-items:flex-end;
    gap:8px;
}

.msg-wrapper.me{
    justify-content:flex-end;
}

.msg-wrapper.other{
    justify-content:flex-start;
}

.msg{
    max-width:76%;
    padding:8px 12px;
    font-size:13px;
    position:relative;
    box-shadow:0 1px 3px rgba(0,0,0,0.12);
}

/* MY MESSAGE (WHATSAPP GREEN) */
.me .msg{
    background:#d9fdd3;
    border-radius:12px 12px 2px 12px;
    color:#111b21;
}

/* OTHER MESSAGE (WHITE) */
.other .msg{
    background:#ffffff;
    border-radius:12px 12px 12px 2px;
    border:1px solid #f0f0f0;
    color:#111b21;
}

.avatar{
    width:32px;
    height:32px;
    border-radius:50%;
    object-fit:cover;
    border:1px solid #e0e0e0;
    box-shadow:0 1px 3px rgba(0,0,0,0.08);
}

.msg-user-name{
    font-size:11.5px;
    font-weight:700;
    margin-bottom:3px;
    display:flex;
    align-items:center;
    gap:5px;
    flex-wrap:wrap;
}

.me .msg-user-name{
    color:#0b735f;
    justify-content:flex-end;
}

.other .msg-user-name{
    color:#128c7e;
    justify-content:flex-start;
}

.user-dept-pill{
    font-size:9.5px;
    font-weight:600;
    padding:1px 6px;
    border-radius:10px;
    border:1px solid rgba(0,0,0,0.08);
}

.me .user-dept-pill{
    background:#e1f9db;
    color:#0b735f;
    border-color:#bfe9b7;
}

.other .user-dept-pill{
    background:#e6f8f5;
    color:#075e54;
    border-color:#beebe3;
}

.chat-date-separator{
    text-align:center;
    margin:12px 0 10px 0;
    position:relative;
}

.chat-date-separator span{
    background:#ffffff;
    color:#54656f;
    font-size:11px;
    font-weight:700;
    padding:4px 14px;
    border-radius:14px;
    box-shadow:0 1px 3px rgba(0,0,0,0.12);
    display:inline-block;
    border:1px solid #e2e8f0;
}

.chat-share-badge{
    display:inline-flex;
    align-items:center;
    gap:4px;
    font-size:10px;
    font-weight:700;
    color:#54656f;
    margin-bottom:3px;
    text-transform:uppercase;
    letter-spacing:0.3px;
}

.chat-text{
    word-break:break-word;
    white-space:pre-wrap;
    line-height:1.45;
}

/* TIME (WHATSAPP STYLE) */
.chat-time{
    display:flex;
    align-items:center;
    justify-content:flex-end;
    gap:4px;
    font-size:10px;
    color:#667781;
    margin-top:4px;
}

/* ================= CHAT IMAGES ================= */
.chat-img-wrap{
    border-radius:8px;
    overflow:hidden;
    background:#0000000a;
    border:1px solid rgba(0,0,0,0.08);
    position:relative;
    max-width:240px;
}

.chat-img-preview{
    width:100%;
    max-height:220px;
    object-fit:cover;
    cursor:pointer;
    display:block;
    transition:transform 0.2s ease;
}

.chat-img-preview:hover{
    transform:scale(1.02);
}

.chat-img-actions{
    background:#ffffffd9;
    padding:4px 8px;
    font-size:11px;
}

.chat-dl-btn{
    color:#0b735f;
    font-weight:600;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    gap:3px;
}

.chat-dl-btn:hover{
    color:#075e54;
    text-decoration:none;
}

/* ================= CHAT DOCUMENTS ================= */
.chat-file-card{
    background:#f0f2f5;
    border:1px solid #dcdfe4;
    border-radius:8px;
    padding:8px 10px;
    display:flex;
    align-items:center;
    gap:10px;
    min-width:200px;
    max-width:260px;
}

.chat-file-icon{
    width:36px;
    height:36px;
    background:#fff;
    border-radius:6px;
    border:1px solid #e1e4e8;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:18px;
    flex-shrink:0;
}

.chat-file-info{
    flex:1;
    overflow:hidden;
}

.chat-file-name{
    font-size:12px;
    font-weight:600;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
    color:#111b21;
}

.chat-file-meta{
    font-size:11px;
    display:flex;
    align-items:center;
    gap:8px;
    margin-top:2px;
}

/* ================= PRE-SEND ATTACHMENT PREVIEW ================= */
.chat-attachment-preview{
    background:#f8fafc;
    border-top:1px solid #e2e8f0;
    padding:8px 12px;
}

.preview-pill{
    display:inline-flex;
    align-items:center;
    gap:10px;
    background:#fff;
    border:1px solid #cbd5e1;
    border-radius:8px;
    padding:4px 10px;
    max-width:100%;
}

.preview-thumb{
    width:34px;
    height:34px;
    border-radius:6px;
    object-fit:cover;
}

/* ================= INPUT FOOTER ================= */
.chat-footer{
    display:flex;
    align-items:center;
    gap:8px;
    padding:10px 14px;
    border-top:1px solid #eee;
    background:#f0f2f5;
}

.chat-footer input[type="text"]{
    flex:1;
    border-radius:24px;
    border:1px solid #d1d7db;
    padding:9px 16px;
    outline:none;
    font-size:13.5px;
    background:#fff;
    transition:border-color 0.2s ease;
}

.chat-footer input[type="text"]:focus{
    border-color:#128c7e;
}

/* icons */
.icon-btn{
    width:40px;
    height:40px;
    border-radius:50%;
    background:#fff;
    border:1px solid #e0e0e0;
    display:flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    color:#54656f;
    transition:all 0.2s ease;
    margin:0;
}

.icon-btn:hover{
    background:#e9edef;
    color:#111b21;
}

.send-btn{
    background:#128c7e !important;
    color:#fff !important;
    border:none;
    box-shadow:0 2px 6px rgba(18,140,126,0.3);
}

.send-btn:hover{
    background:#075e54 !important;
    color:#fff !important;
}

/* ================= HEADER BAR ================= */
.page-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:16px 20px;
    margin-bottom:15px;
    background: linear-gradient(135deg, #1e293b, #334155);
    color:#fff;
    border-radius:14px;
    box-shadow:0 4px 14px rgba(0,0,0,0.1);
}

.page-header h3{
    margin:0;
    font-size:20px;
    font-weight:700;
}

.info-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:10px;
}

.info-item{
    background:#f9fafb;
    padding:10px 12px;
    border-radius:8px;
    border:1px solid #f0f0f0;
}

.info-item .label{
    display:block;
    font-size:11px;
    color:#6b7280;
    margin-bottom:2px;
    text-transform:uppercase;
    font-weight:600;
}

.info-item .value{
    font-size:13px;
    font-weight:600;
    color:#111827;
}

.ticket-img-container{
    position:relative;
    display:inline-block;
    border-radius:10px;
    overflow:hidden;
}

.ticket-img-container:hover .img-hover-overlay{
    opacity:1;
}

.img-hover-overlay{
    position:absolute;
    top:0;
    left:0;
    right:0;
    bottom:0;
    background:rgba(0,0,0,0.45);
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:13px;
    font-weight:600;
    opacity:0;
    transition:opacity 0.2s ease;
    gap:5px;
}

</style>
@endsection

@section('content')

@php
$user = Auth::user();
$isAdmin = $user->user_type == 'admin';
@endphp

@if($isAdmin || hasPermission('ticket_view_all') || hasPermission('ticket_view_own') || hasPermission('ticket_add'))

<div class="page-wrap">
    
    {{-- ================= HEADER ================= --}}
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3><i class="fa fa-ticket-alt me-2"></i> Complaint #{{ $ticket->id }} - {{ $ticket->title }}</h3>
            <small style="opacity:0.85;">Created on {{ $ticket->created_at->format('d M Y, h:i A') }} by {{ $ticket->creator->name ?? 'System' }}</small>
        </div>
        <div class="d-flex align-items-center" style="gap:10px;">
            <button type="button" class="btn btn-sm btn-light shadow-sm" onclick="window.location.reload();" title="Refresh Page">
                <i class="fa fa-rotate-right me-1"></i> Refresh
            </button>
            <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline-light">
                <i class="fa fa-arrow-left me-1"></i> Back to Complaints
            </a>
        </div>
    </div>

<div class="ticket-grid">

{{-- ================= LEFT PANEL ================= --}}
<div class="left-panel">

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link active font-weight-bold" data-toggle="tab" href="#overview">
                <i class="fa fa-circle-info mr-1"></i> Overview & Attachment
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link font-weight-bold" data-toggle="tab" href="#activities">
                <i class="fa fa-clock-rotate-left mr-1"></i> Activities Timeline ({{ count($activities) }})
            </a>
        </li>
    </ul>

    <div class="tab-content">

        {{-- ================= OVERVIEW ================= --}}
        <div class="tab-pane fade show active" id="overview">

            {{-- BASIC DETAILS CARD --}}
            <div class="card-box">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <h6 class="m-0 font-weight-bold" style="color:#1e293b;">
                        <i class="fa fa-file-text text-primary mr-1"></i> Complaint Information
                    </h6>
                    <span class="badge badge-primary px-3 py-1" style="border-radius:20px; font-size:12px; text-transform:capitalize;">
                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                    </span>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Title</span>
                        <span class="value">{{ $ticket->title }}</span>
                    </div>

                    <div class="info-item">
                        <span class="label">Subject</span>
                        <span class="value">{{ $ticket->subject }}</span>
                    </div>

                    <div class="info-item">
                        <span class="label">Complaint Type</span>
                        <span class="value text-capitalize">{{ $ticket->ticket_type }}</span>
                    </div>

                    <div class="info-item">
                        <span class="label">Priority</span>
                        <span class="value">
                            <span class="badge-soft {{ $ticket->priority=='high'?'high':($ticket->priority=='medium'?'medium':'low') }}">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </span>
                    </div>

                    <div class="info-item">
                        <span class="label">Created By</span>
                        <span class="value">{{ $ticket->creator->name ?? '-' }}</span>
                    </div>

                    <div class="info-item">
                        <span class="label">Department</span>
                        <span class="value">{{ $ticket->department->dept_name ?? '-' }}</span>
                    </div>

                    <div class="info-item">
                        <span class="label">Location</span>
                        <span class="value">{{ $ticket->location->name ?? '-' }}</span>
                    </div>

                    <div class="info-item">
                        <span class="label">Created Date</span>
                        <span class="value">{{ $ticket->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                </div>
            </div>

            {{-- COMPLAINT DESCRIPTION --}}
            <div class="card-box">
                <h6 class="mb-2 font-weight-bold" style="color:#1e293b; font-size:13.5px;">
                    <i class="fa fa-align-left text-primary mr-1"></i> Complaint Description
                </h6>
                <div class="p-3 bg-light rounded" style="font-size:13px; line-height:1.6; color:#334155; white-space:pre-wrap; border:1px solid #e2e8f0; max-height:220px; overflow-y:auto;">{{ $ticket->description ?: 'No detailed description provided.' }}</div>
            </div>

            {{-- COMPLAINT ATTACHMENT CARD (UPLOADED DURING COMPLAINT CREATION) --}}
            <div class="card-box">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="m-0 font-weight-bold" style="color:#1e293b; font-size:13.5px;">
                        <i class="fa fa-paperclip text-primary mr-1"></i> Complaint Attachment / File
                    </h6>
                    @if($ticket->attachment)
                        <span class="badge badge-success px-2 py-1" style="font-size:11px;">Attached</span>
                    @else
                        <span class="badge badge-secondary px-2 py-1" style="font-size:11px;">No Attachment</span>
                    @endif
                </div>

                @if($ticket->attachment)
                    @php
                        $ticketExt = strtolower(pathinfo($ticket->attachment, PATHINFO_EXTENSION));
                        $isTicketImg = in_array($ticketExt, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
                        $ticketFileUrl = asset('uploads/tickets/' . $ticket->attachment);
                        $ticketFileName = preg_replace('/^\d+_/', '', $ticket->attachment);
                    @endphp

                    <div class="p-3 rounded" style="background:#f8fafc; border:1px solid #e2e8f0;">
                        @if($isTicketImg)
                            <div class="d-flex flex-wrap align-items-center" style="gap:15px;">
                                <div class="ticket-img-container" style="cursor:pointer;" onclick="openMediaModal('{{ $ticketFileUrl }}', '{{ $ticketFileName }}', true)">
                                    <img src="{{ $ticketFileUrl }}" alt="Attachment" style="width:140px; height:100px; object-fit:cover; border-radius:8px; border:1px solid #cbd5e1;">
                                    <div class="img-hover-overlay"><i class="fa fa-magnifying-glass-plus"></i> View</div>
                                </div>
                                <div class="flex-grow-1" style="min-width:180px;">
                                    <div class="font-weight-bold text-dark mb-1" style="word-break:break-all; font-size:13px;">{{ $ticketFileName }}</div>
                                    <small class="text-muted d-block mb-2 text-uppercase font-weight-bold">{{ $ticketExt }} Image</small>
                                    <div class="d-flex flex-wrap" style="gap:8px;">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="openMediaModal('{{ $ticketFileUrl }}', '{{ $ticketFileName }}', true)">
                                            <i class="fa fa-eye mr-1"></i> View Image
                                        </button>
                                        <a href="{{ $ticketFileUrl }}" download="{{ $ticketFileName }}" class="btn btn-sm btn-primary">
                                            <i class="fa fa-download mr-1"></i> Download
                                        </a>
                                        <a href="{{ $ticketFileUrl }}" target="_blank" class="btn btn-sm btn-light border">
                                            <i class="fa fa-external-link-alt mr-1"></i> Open Tab
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="d-flex align-items-center" style="gap:14px;">
                                <div style="width:48px; height:48px; border-radius:10px; display:flex; align-items:center; justify-content:center; background:#fff; border:1px solid #e2e8f0; font-size:24px; flex-shrink:0;">
                                    @if($ticketExt == 'pdf')
                                        <i class="fa fa-file-pdf text-danger"></i>
                                    @elseif(in_array($ticketExt, ['doc', 'docx']))
                                        <i class="fa fa-file-word text-primary"></i>
                                    @elseif(in_array($ticketExt, ['xls', 'xlsx', 'csv']))
                                        <i class="fa fa-file-excel text-success"></i>
                                    @elseif(in_array($ticketExt, ['zip', 'rar']))
                                        <i class="fa fa-file-zipper text-warning"></i>
                                    @else
                                        <i class="fa fa-file-lines text-secondary"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1" style="min-width:0;">
                                    <div class="font-weight-bold text-dark text-truncate mb-1" title="{{ $ticketFileName }}" style="font-size:13px;">{{ $ticketFileName }}</div>
                                    <small class="text-muted d-block mb-2 text-uppercase font-weight-bold">{{ $ticketExt }} Document</small>
                                    <div class="d-flex flex-wrap" style="gap:8px;">
                                        @if($ticketExt == 'pdf')
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="openMediaModal('{{ $ticketFileUrl }}', '{{ $ticketFileName }}', false)">
                                                <i class="fa fa-eye mr-1"></i> Preview PDF
                                            </button>
                                        @endif
                                        <a href="{{ $ticketFileUrl }}" download="{{ $ticketFileName }}" class="btn btn-sm btn-primary">
                                            <i class="fa fa-download mr-1"></i> Download
                                        </a>
                                        <a href="{{ $ticketFileUrl }}" target="_blank" class="btn btn-sm btn-light border">
                                            <i class="fa fa-external-link-alt mr-1"></i> Open File
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="p-3 text-center text-muted rounded" style="background:#f8fafc; border:1px dashed #cbd5e1; font-size:12.5px;">
                        <i class="fa fa-folder-open mb-1 d-block" style="font-size:20px; opacity:0.4;"></i>
                        No file or attachment was uploaded with this complaint.
                    </div>
                @endif
            </div>

            {{-- COMPLAINED EMPLOYEES --}}
            <div class="card-box">
                <h6 style="margin-bottom:12px; font-weight:700; color:#1e293b; font-size:13.5px;">
                    <i class="fa fa-users text-primary mr-1"></i> Complained Employee(s)
                </h6>

                <div style="display:flex; align-items:center; flex-wrap:wrap; gap:8px;">
                    @php
                        $assignments = $ticket->assignments;
                        $showLimit = 8;
                    @endphp

                    @forelse($assignments->take($showLimit) as $assign)
                        @php
                            $emp = $assign->employee ?? null;
                            $img = $emp && $emp->image
                                ? asset('uploads/employees/'.$emp->image)
                                : asset('uploads/employees/user.png');
                            $empName = $emp->user->name ?? 'Unknown';
                        @endphp

                        <div class="d-flex align-items-center p-1 px-2 border rounded-pill bg-light" title="{{ $empName }}" style="gap:6px;">
                            <img src="{{ $img }}" style="width:28px; height:28px; border-radius:50%; object-fit:cover;">
                            <span style="font-size:12px; font-weight:600; color:#334155;">{{ $empName }}</span>
                        </div>
                    @empty
                        <span class="text-muted" style="font-size:12.5px;">No specific employee assigned.</span>
                    @endforelse

                    @if($assignments->count() > $showLimit)
                        <div style="padding:4px 10px; border-radius:20px; background:#e2e8f0; font-size:12px; font-weight:700;">
                            +{{ $assignments->count() - $showLimit }} more
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- ================= ACTIVITIES TAB ================= --}}
        <div class="tab-pane fade" id="activities">
            <div class="card-box">
                <h6 class="m-0 font-weight-bold" style="color:#1e293b;">
                    <i class="fa fa-clock-rotate-left text-primary mr-1"></i> Complaint Activity Timeline
                </h6>
            </div>

            @forelse($activities as $act)
                @php
                    $empName = null;
                    if($act->table_name == 'ticket_assignments') {
                        $assign = \App\Models\TicketAssignment::with('employee.user')->find($act->table_id);
                        $empName = $assign->employee->user->name ?? null;
                    }
                @endphp

                <div class="card-box" style="font-size:13px; padding:12px 14px;">
                    <div style="display:flex; gap:10px;">
                        <div style="width:9px; height:9px; border-radius:50%; background:#2563eb; margin-top:5px; flex-shrink:0;"></div>
                        <div>
                            <b style="text-transform:capitalize; color:#1e293b;">
                                {{ str_replace('_', ' ', $act->operation) }}
                            </b>
                            <div style="color:#475569; margin:2px 0;">
                                @if($empName)
                                    {{ str_replace('employee ID', $empName, $act->description) }}
                                @else
                                    {{ $act->description }}
                                @endif
                            </div>
                            <small style="color:#94a3b8;">
                                <i class="fa fa-clock mr-1"></i> {{ \Carbon\Carbon::parse($act->created_at)->format('d M Y, h:i A') }}
                            </small>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card-box text-center text-muted" style="font-size:13px;">
                    No activities recorded yet.
                </div>
            @endforelse
        </div>

    </div>

</div>

{{-- ================= RIGHT PANEL: CHAT / DISCUSSION ================= --}}
<div class="right-panel">

    <div class="chat-header">
        <div class="chat-header-left">
            <span class="live-indicator"></span>
            <span>💬 Discussion & Files</span>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-link text-white p-0" onclick="fetchNewComments(true);" title="Refresh chat messages">
                <i class="fa fa-rotate-right" id="chatRefreshIcon"></i>
            </button>
        </div>
    </div>

    {{-- CHAT BODY (MESSAGES) --}}
    <div class="chat-body" id="chatBox">

        @php
            $lastChatDate = null;
        @endphp

        @foreach($ticket->comments->sortBy('created_at') as $msg)
            @php
                $msgDate = $msg->created_at->format('Y-m-d');
                $isMe = $msg->user_id == auth()->id();
                $emp = $msg->user->employee ?? null;
                $avatar = ($emp && $emp->image && file_exists(public_path('uploads/employees/'.$emp->image)))
                    ? asset('uploads/employees/'.$emp->image)
                    : asset('uploads/employees/user.png');

                $userDept = $emp->department->dept_name ?? ($msg->user && $msg->user->user_type == 'admin' ? 'Admin' : '');

                $dateDividerLabel = $msg->created_at->isToday() 
                    ? 'Today' 
                    : ($msg->created_at->isYesterday() ? 'Yesterday' : $msg->created_at->format('d M Y'));
            @endphp

            {{-- DATE DIVIDER --}}
            @if($lastChatDate !== $msgDate)
                <div class="chat-date-separator" data-date="{{ $msgDate }}">
                    <span><i class="fa fa-calendar-day me-1 text-muted"></i> {{ $dateDividerLabel }}</span>
                </div>
                @php $lastChatDate = $msgDate; @endphp
            @endif

            <div class="msg-wrapper {{ $isMe ? 'me' : 'other' }}" data-id="{{ $msg->id }}" data-date="{{ $msgDate }}">
                @if(!$isMe)
                    <img src="{{ $avatar }}" class="avatar" alt="{{ $msg->user->name ?? 'User' }}" title="{{ $msg->user->name ?? 'User' }}{{ $userDept ? ' ('.$userDept.')' : '' }}">
                @endif

                <div class="msg">
                    <div class="msg-user-name">
                        <span>{{ $isMe ? 'You' : ($msg->user->name ?? 'User') }}</span>
                        @if($userDept)
                            <span class="user-dept-pill">{{ $userDept }}</span>
                        @endif
                    </div>

                    {{-- ATTACHMENT DISPLAY (WHATSAPP STYLE) --}}
                    @if($msg->attachment)
                        @php
                            $ext = strtolower(pathinfo($msg->attachment, PATHINFO_EXTENSION));
                            $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp','svg']);
                            $fileUrl = asset('uploads/comments/'.$msg->attachment);
                            $cleanName = preg_replace('/^\d+_/', '', $msg->attachment);

                            $fileSizeFormatted = '';
                            if(file_exists(public_path('uploads/comments/'.$msg->attachment))) {
                                $bytes = @filesize(public_path('uploads/comments/'.$msg->attachment));
                                if($bytes !== false) {
                                    $fileSizeFormatted = ($bytes < 1048576) ? round($bytes/1024, 1).' KB' : round($bytes/1048576, 1).' MB';
                                }
                            }
                        @endphp

                        @if($isImg)
                            <div class="chat-share-badge">
                                <i class="fa fa-image text-info"></i> Shared Image
                            </div>
                            <div class="chat-img-wrap mb-2">
                                <img src="{{ $fileUrl }}" alt="{{ $cleanName }}" class="chat-img-preview" onclick="openMediaModal('{{ $fileUrl }}', '{{ $cleanName }}', true)">
                                <div class="chat-img-actions d-flex justify-content-between align-items-center">
                                    <div class="text-truncate mr-2" style="max-width:150px; font-size:11px;" title="{{ $cleanName }}">
                                        <b>{{ $cleanName }}</b>
                                        @if($fileSizeFormatted)
                                            <span class="text-muted d-block" style="font-size:9.5px;">{{ $fileSizeFormatted }}</span>
                                        @endif
                                    </div>
                                    <a href="{{ $fileUrl }}" download="{{ $cleanName }}" class="chat-dl-btn" title="Download Image">
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="chat-share-badge">
                                <i class="fa fa-paperclip text-primary"></i> Shared Document
                            </div>
                            <div class="chat-file-card mb-2">
                                <div class="chat-file-icon">
                                    @if($ext == 'pdf')
                                        <i class="fa fa-file-pdf text-danger"></i>
                                    @elseif(in_array($ext, ['doc','docx']))
                                        <i class="fa fa-file-word text-primary"></i>
                                    @elseif(in_array($ext, ['xls','xlsx','csv']))
                                        <i class="fa fa-file-excel text-success"></i>
                                    @elseif(in_array($ext, ['zip','rar']))
                                        <i class="fa fa-file-zipper text-warning"></i>
                                    @else
                                        <i class="fa fa-file-lines text-secondary"></i>
                                    @endif
                                </div>
                                <div class="chat-file-info">
                                    <div class="chat-file-name" title="{{ $cleanName }}">{{ $cleanName }}</div>
                                    <div class="chat-file-meta">
                                        <span class="badge badge-light border text-uppercase" style="font-size:9px;">{{ $ext }}</span>
                                        @if($fileSizeFormatted)
                                            <span class="text-muted" style="font-size:10px;">{{ $fileSizeFormatted }}</span>
                                        @endif
                                        <a href="{{ $fileUrl }}" download="{{ $cleanName }}" class="text-primary font-weight-bold" style="font-size:11px;">
                                            <i class="fa fa-download"></i> Save
                                        </a>
                                        @if($ext == 'pdf')
                                            <a href="javascript:void(0)" onclick="openMediaModal('{{ $fileUrl }}', '{{ $cleanName }}', false)" class="text-danger font-weight-bold" style="font-size:11px;">
                                                <i class="fa fa-eye"></i> View
                                            </a>
                                        @else
                                            <a href="{{ $fileUrl }}" target="_blank" class="text-secondary font-weight-bold" style="font-size:11px;">
                                                <i class="fa fa-external-link-alt"></i> Open
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    {{-- TEXT MESSAGE --}}
                    @if(!empty($msg->comment))
                        <div class="chat-text">{{ $msg->comment }}</div>
                    @endif

                    <div class="chat-time" title="{{ $msg->created_at->format('d M Y, h:i A') }}">
                        <span class="text-muted mr-1" style="font-size:9.5px;">{{ $msg->created_at->format('d M') }}</span>
                        <span>{{ $msg->created_at->format('g:i A') }}</span>
                        @if($isMe)
                            <i class="fa fa-check-double text-primary" style="font-size:10px;"></i>
                        @endif
                    </div>
                </div>

                @if($isMe)
                    <img src="{{ $avatar }}" class="avatar" alt="You" title="You{{ $userDept ? ' ('.$userDept.')' : '' }}">
                @endif
            </div>
        @endforeach

    </div>

    {{-- PRE-SEND ATTACHMENT PREVIEW --}}
    <div class="chat-attachment-preview" id="chatAttachmentPreview" style="display:none;">
        <div class="preview-pill">
            <div id="previewThumbBox"></div>
            <div style="min-width:0; flex:1;">
                <div id="previewFileName" class="font-weight-bold text-truncate" style="font-size:12px; color:#1e293b; max-width:200px;"></div>
                <small id="previewFileSize" class="text-muted" style="font-size:10.5px;"></small>
            </div>
            <button type="button" class="btn btn-sm btn-link text-danger p-0 ml-2" id="cancelAttachmentBtn" title="Remove attachment" style="font-size:16px;">
                <i class="fa fa-times-circle"></i>
            </button>
        </div>
    </div>

    {{-- CHAT INPUT FOOTER --}}
    <div class="chat-footer">
        <label class="icon-btn" for="fileInput" title="Attach file or image">
            <i class="fa fa-paperclip"></i>
            <input type="file" id="fileInput" style="display:none;" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar,.csv">
        </label>

        <input type="text" id="message" placeholder="Type a message... (Press Enter to send)" autocomplete="off">

        <button type="button" class="icon-btn send-btn" id="sendBtn" title="Send message">
            <i class="fa fa-paper-plane" id="sendBtnIcon"></i>
        </button>
    </div>

</div>

</div>
</div>

<!-- ================= MEDIA LIGHTBOX MODAL ================= -->
<div class="modal fade" id="mediaViewerModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius:14px; overflow:hidden;">
            <div class="modal-header py-2 px-3 bg-dark text-white d-flex justify-content-between align-items-center">
                <h6 class="modal-title text-truncate m-0 text-white" id="mediaViewerTitle" style="font-size:13.5px; max-width:65%;">
                    File Preview
                </h6>
                <div class="d-flex align-items-center" style="gap:8px;">
                    <a href="#" id="mediaViewerDownloadBtn" download="" class="btn btn-sm btn-outline-light py-1 px-2" style="font-size:11.5px;">
                        <i class="fa fa-download mr-1"></i> Download
                    </a>
                    <a href="#" id="mediaViewerExternalBtn" target="_blank" class="btn btn-sm btn-outline-light py-1 px-2" style="font-size:11.5px;">
                        <i class="fa fa-external-link-alt mr-1"></i> Open Tab
                    </a>
                    <button type="button" class="close text-white ml-2" data-dismiss="modal" aria-label="Close" style="font-size:22px; opacity:0.8;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
            <div class="modal-body p-0 text-center bg-dark d-flex align-items-center justify-content-center" style="min-height:360px; max-height:80vh; overflow:auto;">
                <div id="mediaViewerContent" class="w-100 h-100 d-flex align-items-center justify-content-center p-2">
                </div>
            </div>
        </div>
    </div>
</div>

@else
<div class="alert alert-danger m-3">You do not have permission to view this complaint.</div>
@endif

@endsection

@section('script')
<script>
$(document).ready(function(){

    const ticketId = "{{ $ticket->id }}";
    const currentUserId = {{ auth()->id() }};
    const defaultUserAvatar = "{{ asset('uploads/employees/user.png') }}";

    function scrollBottom(smooth = false){
        const el = document.getElementById('chatBox');
        if(!el) return;
        if(smooth){
            $(el).animate({ scrollTop: el.scrollHeight }, 300);
        } else {
            el.scrollTop = el.scrollHeight;
        }
    }
    scrollBottom();

    /* ================= OPEN MEDIA MODAL ================= */
    window.openMediaModal = function(url, title, isImg){
        $('#mediaViewerTitle').text(title || 'File Preview');
        $('#mediaViewerDownloadBtn').attr('href', url).attr('download', title || 'download');
        $('#mediaViewerExternalBtn').attr('href', url);

        if(isImg){
            $('#mediaViewerContent').html(`
                <img src="${url}" alt="${title}" style="max-width:100%; max-height:75vh; object-fit:contain; border-radius:6px; box-shadow:0 4px 15px rgba(0,0,0,0.5);">
            `);
        } else {
            $('#mediaViewerContent').html(`
                <iframe src="${url}" style="width:100%; height:75vh; border:none; background:#fff; border-radius:6px;"></iframe>
            `);
        }

        $('#mediaViewerModal').modal('show');
    };

    /* ================= FILE INPUT PREVIEW ================= */
    let selectedFile = null;

    $('#fileInput').on('change', function(){
        const file = this.files[0];
        if(!file) return;

        selectedFile = file;
        const ext = file.name.split('.').pop().toLowerCase();
        const isImg = ['jpg','jpeg','png','gif','webp','bmp','svg'].includes(ext);

        let sizeFormatted = '';
        if(file.size < 1024 * 1024){
            sizeFormatted = (file.size / 1024).toFixed(1) + ' KB';
        } else {
            sizeFormatted = (file.size / (1024 * 1024)).toFixed(1) + ' MB';
        }

        $('#previewFileName').text(file.name);
        $('#previewFileSize').text(sizeFormatted);

        if(isImg){
            const reader = new FileReader();
            reader.onload = function(e){
                $('#previewThumbBox').html(`
                    <img src="${e.target.result}" class="preview-thumb">
                `);
            };
            reader.readAsDataURL(file);
        } else {
            let iconClass = 'fa-file-lines text-secondary';
            if(ext === 'pdf') iconClass = 'fa-file-pdf text-danger';
            else if(['doc','docx'].includes(ext)) iconClass = 'fa-file-word text-primary';
            else if(['xls','xlsx','csv'].includes(ext)) iconClass = 'fa-file-excel text-success';
            else if(['zip','rar'].includes(ext)) iconClass = 'fa-file-zipper text-warning';

            $('#previewThumbBox').html(`
                <div style="width:34px; height:34px; display:flex; align-items:center; justify-content:center; font-size:18px;">
                    <i class="fa ${iconClass}"></i>
                </div>
            `);
        }

        $('#chatAttachmentPreview').slideDown(150);
        $('#message').focus();
    });

    $('#cancelAttachmentBtn').on('click', function(){
        $('#fileInput').val('');
        selectedFile = null;
        $('#chatAttachmentPreview').slideUp(150);
    });

    /* ================= BUILD MESSAGE HTML ================= */
    function renderMessage(msg){
        const isMe = (msg.user_id == currentUserId);
        const avatar = msg.avatar || defaultUserAvatar;
        const userName = isMe ? 'You' : (msg.user_name || 'User');
        const deptPill = msg.user_dept ? `<span class="user-dept-pill">${msg.user_dept}</span>` : '';

        let attachmentHtml = '';
        if(msg.file){
            const ext = (msg.file_ext || msg.file.split('.').pop()).toLowerCase();
            const fileName = msg.file_name || msg.file.split('/').pop();
            const isImg = msg.is_image || ['jpg','jpeg','png','gif','webp','bmp','svg'].includes(ext);
            const fileSizeText = msg.file_size ? `<span class="text-muted d-block" style="font-size:9.5px;">${msg.file_size}</span>` : '';
            const sizeBadge = msg.file_size ? `<span class="text-muted" style="font-size:10px;">${msg.file_size}</span>` : '';

            if(isImg){
                attachmentHtml = `
                    <div class="chat-share-badge">
                        <i class="fa fa-image text-info"></i> Shared Image
                    </div>
                    <div class="chat-img-wrap mb-2">
                        <img src="${msg.file}" alt="${fileName}" class="chat-img-preview" onclick="openMediaModal('${msg.file}', '${fileName}', true)">
                        <div class="chat-img-actions d-flex justify-content-between align-items-center">
                            <div class="text-truncate mr-2" style="max-width:150px; font-size:11px;" title="${fileName}">
                                <b>${fileName}</b>
                                ${fileSizeText}
                            </div>
                            <a href="${msg.file}" download="${fileName}" class="chat-dl-btn" title="Download Image">
                                <i class="fa fa-download"></i>
                            </a>
                        </div>
                    </div>
                `;
            } else {
                let iconClass = 'fa-file-lines text-secondary';
                let previewBtn = '';

                if(ext === 'pdf'){
                    iconClass = 'fa-file-pdf text-danger';
                    previewBtn = `<a href="javascript:void(0)" onclick="openMediaModal('${msg.file}', '${fileName}', false)" class="text-danger font-weight-bold" style="font-size:11px;"><i class="fa fa-eye"></i> View</a>`;
                } else if(['doc','docx'].includes(ext)){
                    iconClass = 'fa-file-word text-primary';
                } else if(['xls','xlsx','csv'].includes(ext)){
                    iconClass = 'fa-file-excel text-success';
                } else if(['zip','rar'].includes(ext)){
                    iconClass = 'fa-file-zipper text-warning';
                }

                if(!previewBtn){
                    previewBtn = `<a href="${msg.file}" target="_blank" class="text-secondary font-weight-bold" style="font-size:11px;"><i class="fa fa-external-link-alt"></i> Open</a>`;
                }

                attachmentHtml = `
                    <div class="chat-share-badge">
                        <i class="fa fa-paperclip text-primary"></i> Shared Document
                    </div>
                    <div class="chat-file-card mb-2">
                        <div class="chat-file-icon">
                            <i class="fa ${iconClass}"></i>
                        </div>
                        <div class="chat-file-info">
                            <div class="chat-file-name" title="${fileName}">${fileName}</div>
                            <div class="chat-file-meta">
                                <span class="badge badge-light border text-uppercase" style="font-size:9px;">${ext}</span>
                                ${sizeBadge}
                                <a href="${msg.file}" download="${fileName}" class="text-primary font-weight-bold" style="font-size:11px;">
                                    <i class="fa fa-download"></i> Save
                                </a>
                                ${previewBtn}
                            </div>
                        </div>
                    </div>
                `;
            }
        }

        const textHtml = msg.message ? `<div class="chat-text">${$('<div>').text(msg.message).html().replace(/\n/g, '<br>')}</div>` : '';
        const checkIcon = isMe ? `<i class="fa fa-check-double text-primary" style="font-size:10px;"></i>` : '';
        const dateAttr = msg.date || '';
        const fullDt = msg.full_datetime || msg.time;
        const dateSub = msg.date ? `<span class="text-muted mr-1" style="font-size:9.5px;">${msg.date.substring(0, 6)}</span>` : '';

        return `
            <div class="msg-wrapper ${isMe ? 'me' : 'other'}" data-id="${msg.id}" data-date="${dateAttr}">
                ${!isMe ? `<img src="${avatar}" class="avatar" alt="${userName}" title="${userName}${msg.user_dept ? ' ('+msg.user_dept+')' : ''}">` : ''}
                <div class="msg">
                    <div class="msg-user-name">
                        <span>${userName}</span>
                        ${deptPill}
                    </div>
                    ${attachmentHtml}
                    ${textHtml}
                    <div class="chat-time" title="${fullDt}">
                        ${dateSub}
                        <span>${msg.time}</span>
                        ${checkIcon}
                    </div>
                </div>
                ${isMe ? `<img src="${avatar}" class="avatar" alt="You" title="You${msg.user_dept ? ' ('+msg.user_dept+')' : ''}">` : ''}
            </div>
        `;
    }

    function appendMessageWithDate(msg){
        const dateStr = msg.date || '';
        const dateDividerLabel = msg.date_divider || msg.date || 'Today';

        if(dateStr && $(`#chatBox .chat-date-separator[data-date="${dateStr}"]`).length === 0){
            $('#chatBox').append(`
                <div class="chat-date-separator" data-date="${dateStr}">
                    <span><i class="fa fa-calendar-day me-1 text-muted"></i> ${dateDividerLabel}</span>
                </div>
            `);
        }

        $('#chatBox').append(renderMessage(msg));
    }

    /* ================= SEND MESSAGE ================= */
    let isSending = false;

    function sendMessage(){
        if(isSending) return;

        const msgText = $('#message').val().trim();
        const file = $('#fileInput')[0].files[0];

        if(!msgText && !file){
            $('#message').focus();
            return;
        }

        isSending = true;
        $('#sendBtn').prop('disabled', true);
        $('#sendBtnIcon').removeClass('fa-paper-plane').addClass('fa-spinner fa-spin');

        const formData = new FormData();
        formData.append('_token', "{{ csrf_token() }}");
        formData.append('comment', msgText);
        if(file){
            formData.append('attachment', file);
        }

        $.ajax({
            url: "/ticket/" + ticketId + "/comment",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(res){
                if(res.success){
                    $('#message').val('');
                    $('#fileInput').val('');
                    selectedFile = null;
                    $('#chatAttachmentPreview').slideUp(150);

                    // Check if already in DOM
                    if($(`#chatBox .msg-wrapper[data-id="${res.id}"]`).length === 0){
                        appendMessageWithDate(res);
                    }
                    scrollBottom(true);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: res.error || 'Failed to send message'
                    });
                }
            },
            error: function(xhr){
                let err = 'Failed to send message. Please try again.';
                if(xhr.responseJSON && xhr.responseJSON.error){
                    err = xhr.responseJSON.error;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err
                });
            },
            complete: function(){
                isSending = false;
                $('#sendBtn').prop('disabled', false);
                $('#sendBtnIcon').removeClass('fa-spinner fa-spin').addClass('fa-paper-plane');
                $('#message').focus();
            }
        });
    }

    $('#sendBtn').on('click', function(e){
        e.preventDefault();
        sendMessage();
    });

    $('#message').on('keydown', function(e){
        if(e.key === 'Enter' && !e.shiftKey){
            e.preventDefault();
            sendMessage();
        }
    });

    /* ================= POLLING NEW COMMENTS ================= */
    window.fetchNewComments = function(showSpinner = false){
        if(showSpinner){
            $('#chatRefreshIcon').addClass('fa-spin');
        }

        $.ajax({
            url: "/ticket/" + ticketId + "/comments",
            type: "GET",
            dataType: "json",
            success: function(comments){
                if(!Array.isArray(comments)) return;

                let hasNew = false;
                comments.forEach(function(c){
                    if($(`#chatBox .msg-wrapper[data-id="${c.id}"]`).length === 0){
                        appendMessageWithDate(c);
                        hasNew = true;
                    }
                });

                if(hasNew){
                    scrollBottom(true);
                }
            },
            complete: function(){
                if(showSpinner){
                    setTimeout(function(){
                        $('#chatRefreshIcon').removeClass('fa-spin');
                    }, 500);
                }
            }
        });
    };

    // Poll every 6 seconds for new messages
    setInterval(function(){
        fetchNewComments(false);
    }, 6000);

});
</script>
@endsection
