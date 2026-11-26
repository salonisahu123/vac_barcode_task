@extends('layouts.master')

@section('title','Tasks')

@section('css')
<link rel="stylesheet" href="{{ asset('css/task.css') }}">
<style>
/* Modern styling for Task Management (No DataTables) */
.task-table-wrapper {
    position: relative;
    border-radius: 12px;
    background: #fff;
    border: 1px solid #e2e8f0;
}
.task-table-wrapper.loading #taskLoadingOverlay {
    display: flex !important;
}
#taskLoadingOverlay {
    display: none;
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.75);
    z-index: 20;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(2px);
    border-radius: 12px;
}
.task-table {
    margin-bottom: 0;
}
.task-table thead th {
    background: #f8fafc;
    color: #475569;
    font-size: 12.5px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    border-top: none;
    border-bottom: 2px solid #e2e8f0;
    padding: 11px 12px;
    white-space: nowrap;
}
.task-table tbody td {
    padding: 10px 12px;
    vertical-align: middle;
    border-top: 1px solid #f1f5f9;
}
.task-table tbody tr:hover {
    background-color: #f8fafc;
}
.filter-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}
.filter-label {
    font-size: 12px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 4px;
    display: block;
}
.export-btn {
    font-size: 12.5px;
    padding: 6px 12px;
    border-radius: 6px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
@media print {
    .midde_cont .x-sidebar, .x-header, .filter-card, .btn, .d-flex.justify-content-between, .pagination, #taskPaginationContainer {
        display: none !important;
    }
    .task-table-wrapper {
        border: none !important;
        box-shadow: none !important;
    }
    .task-table {
        width: 100% !important;
    }
}
</style>
@endsection

@section('content')

<x-breadcrumb title="Task Management" page="Task List" />

@php
$user = Auth::user();
$isAdmin = $user->user_type == 'admin';
@endphp

@if($isAdmin || hasPermission('task_view_all') || hasPermission('task_view_own'))

<div class="mt-2"></div>

<div class="row">
    <div class="col-md-12">

        <div class="white_shd full margin_bottom_30 p-3 shadow-sm" style="border-radius:12px;">

            <!-- TOP ACTION BAR -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:10px;">

                <div class="d-flex flex-wrap align-items-center" style="gap:8px;">
                    {{-- ADD TASK PERMISSION --}}
                    @if($isAdmin || hasPermission('task_add'))
                    <a href="{{ route('tasks.create') }}"
                       class="btn btn-primary btn-sm d-flex align-items-center"
                       style="border-radius:6px; font-weight:500; padding:6px 14px;">
                        <i class="fa fa-plus me-1"></i> Create Task
                    </a>
                    @endif

                    <!-- EXPORT OPTIONS -->
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle export-btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-radius:6px;">
                            <i class="fa fa-download text-success"></i> Export Data
                        </button>
                        <div class="dropdown-menu shadow-sm" style="border-radius:8px; font-size:13px;">
                            <a class="dropdown-item py-2" href="#" id="btn_export_excel">
                                <i class="fa fa-file-excel-o text-success me-2"></i> Export to Excel (.xlsx)
                            </a>
                            <a class="dropdown-item py-2" href="#" id="btn_export_csv">
                                <i class="fa fa-file-text-o text-info me-2"></i> Export to CSV
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item py-2" href="#" id="btn_print_table">
                                <i class="fa fa-print text-dark me-2"></i> Print View
                            </a>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    <span class="badge px-3 py-2" style="background:#f1f5f9; color:#334155; font-size:12.5px; border-radius:6px; font-weight:500;">
                        <i class="fa fa-tasks text-primary me-1"></i> Tasks: <span id="totalBadgeCount" class="font-weight-bold text-primary">{{ $tasks->total() }}</span>
                    </span>
                </div>

            </div>
            
            <!--{ ================== FILTER START =============================}-->
            <div class="filter-card p-3 mb-3">

                <div class="row align-items-end" style="row-gap:12px;">

                    <!-- Search Filter -->
                    <div class="col-lg-3 col-md-6">
                        <label class="filter-label"><i class="fa fa-search text-muted me-1"></i> Search Filter</label>
                        <input type="text" id="filter_search" class="form-control" placeholder="Search title, subtask, employee..." style="border-radius:6px; font-size:13px; height:38px;">
                    </div>

                    <!-- Location Filter -->
                    <div class="col-lg-2 col-md-6">
                        <label class="filter-label">Location</label>
                        <select id="filter_location" class="form-control" style="border-radius:6px; font-size:13px; height:38px;">
                            <option value="">All Locations</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Group Filter -->
                    <div class="col-lg-2 col-md-6">
                        <label class="filter-label">Group</label>
                        <select id="filter_group" class="form-control" style="border-radius:6px; font-size:13px; height:38px;">
                            <option value="">All Groups</option>
                            @foreach($groups as $grp)
                                <option value="{{ $grp->id }}">{{ $grp->group_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Employee Filter -->
                    <div class="col-lg-2 col-md-6">
                        <label class="filter-label">Employee</label>
                        <select id="filter_user" class="form-control" style="border-radius:6px; font-size:13px; height:38px;">
                            <option value="">All Employees</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->user->name ?? 'Unknown' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-lg-2 col-md-6">
                        <label class="filter-label">Status</label>
                        <select id="filter_status" class="form-control" style="border-radius:6px; font-size:13px; height:38px;">
                            <option value="pending_in_progress" selected>Pending & In Progress</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="all">All Statuses</option>
                        </select>
                    </div>

                    <!-- Reset Button -->
                    <div class="col-lg-1 col-md-6">
                        <button id="btn_reset_filter" class="btn btn-light border w-100" title="Reset all filters" style="border-radius:6px; height:38px; padding:0; display:flex; align-items:center; justify-content:center;">
                            <i class="fa fa-refresh text-secondary me-1"></i> <span class="d-lg-none small">Reset</span>
                        </button>
                    </div>

                </div>

                <!-- Per Page selector and counter summary -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-2 border-top">
                    <div class="d-flex align-items-center" style="gap:8px;">
                        <span class="text-muted small" style="font-size:12px;">Records Per Page:</span>
                        <select id="filter_per_page" class="form-select form-select-sm" style="width:75px; font-size:12px; height:30px; border-radius:6px; border:1px solid #cbd5e1; padding:2px 8px;">
                            <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ ($perPage ?? 10) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ ($perPage ?? 10) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ ($perPage ?? 10) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>

                    <div class="text-muted small">
                        Showing results with active filters applied.
                    </div>
                </div>

            </div>
            <!--{ ================== FILTER END =============================}-->

            <!-- TABLE CONTAINER -->
            <div class="task-table-wrapper shadow-sm">

                <!-- Loading Overlay Spinner -->
                <div id="taskLoadingOverlay">
                    <div class="text-center p-3 rounded" style="background:#ffffff; box-shadow:0 8px 24px rgba(0,0,0,0.12); border:1px solid #e2e8f0;">
                        <div class="spinner-border text-primary" role="status" style="width:2rem; height:2rem;">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <div class="small font-weight-bold text-dark mt-2">Loading tasks...</div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table task-table align-middle" id="taskTable">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:45px;">#</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Sub Task</th>
                                <th>Location</th>
                                <th>Group</th>
                                <th>From</th>
                                <th>To Date</th>
                                <th>Assigned</th>
                                <th>Created By</th>
                                <th style="min-width:180px;">Action</th>
                                <th style="min-width:145px;">Status</th>
                            </tr>
                        </thead>

                        <tbody id="taskTableBody">
                            @include('tasks.partials.rows', ['tasks' => $tasks, 'isAdmin' => $isAdmin, 'subtasks' => $subtasks])
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION CONTAINER -->
                <div class="p-3 bg-light border-top" id="taskPaginationContainer">
                    @include('tasks.partials.pagination', ['tasks' => $tasks])
                </div>

            </div>

        </div>

    </div>
</div>

@else

<div class="alert alert-danger" style="border-radius:10px;">
    <i class="fa fa-exclamation-triangle me-2"></i> You do not have permission to view tasks list.
</div>

@endif

<!--//////////////////////// Remark Modal ////////////////////////////-->
<div class="modal fade" id="remarkModal" tabindex="-1" role="dialog" aria-labelledby="remarkModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius:12px; border:none; box-shadow:0 12px 35px rgba(0,0,0,0.15); overflow:hidden;">

            <div class="modal-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                <h5 class="modal-title font-weight-bold text-dark" id="remarkModalTitle" style="font-size:16px;">
                    <i class="fa fa-commenting text-primary me-2"></i> Add Remark
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-4">
                <input type="hidden" id="remark_task_id">

                <div class="p-2 mb-3 rounded" style="background:#f1f5f9; border-left:4px solid #0d6efd;">
                    <div class="text-muted small" style="font-size:11.5px; text-transform:uppercase; letter-spacing:0.4px;">Task Title:</div>
                    <div id="remark_task_title" class="font-weight-bold text-dark" style="font-size:14px;"></div>
                </div>

                <div class="form-group mb-0">
                    <label for="remark_text" class="font-weight-bold text-dark" style="font-size:13px;">
                        Remark Description <span class="text-danger">*</span>
                    </label>
                    <textarea id="remark_text" class="form-control" rows="4" placeholder="Enter remark or progress notes here..." style="border-radius:8px; font-size:13px;"></textarea>
                </div>
            </div>

            <div class="modal-footer" style="background:#f8fafc; border-top:1px solid #e2e8f0;">
                <button type="button" class="btn btn-light btn-sm px-3" data-dismiss="modal" style="border-radius:6px;">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm px-4" id="submitRemark" style="border-radius:6px; font-weight:500;">
                    <i class="fa fa-paper-plane me-1"></i> Submit Remark
                </button>
            </div>

        </div>
    </div>
</div>

@endsection

@section('script')

<script>
$(document).ready(function () {

    // Current pagination state
    let currentPage = {{ $tasks->currentPage() }};
    let searchDebounceTimer = null;

    // Toast alert setup
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2500,
        timerProgressBar: true
    });

    // Helper: Collect all active filter data
    function getFilterData(page = 1) {
        return {
            search: $('#filter_search').val(),
            location_id: $('#filter_location').val(),
            group_id: $('#filter_group').val(),
            employee_id: $('#filter_user').val(),
            status: $('#filter_status').val(),
            per_page: $('#filter_per_page').val(),
            page: page
        };
    }

    // Load Tasks with server-side pagination & filtering
    function loadTasks(page = 1, showOverlay = true) {
        currentPage = page;
        let data = getFilterData(page);

        if (showOverlay) {
            $('.task-table-wrapper').addClass('loading');
        }

        $.ajax({
            url: "{{ route('tasks.filter') }}",
            type: "GET",
            data: data,
            success: function(res) {
                $('.task-table-wrapper').removeClass('loading');
                $('#taskTableBody').html(res.html || '');
                $('#taskPaginationContainer').html(res.pagination || '');
                $('#totalBadgeCount').text(res.total || 0);
                currentPage = res.current_page || 1;
            },
            error: function(xhr) {
                $('.task-table-wrapper').removeClass('loading');
                console.error("Task filter error:", xhr);
                Toast.fire({
                    icon: 'error',
                    title: 'Failed to load tasks. Please try again.'
                });
            }
        });
    }

    // Pagination link click (stays on selected page smoothly)
    $(document).on('click', '.task-page-link', function (e) {
        e.preventDefault();
        let targetPage = $(this).data('page');
        if (targetPage && targetPage !== currentPage) {
            loadTasks(targetPage);
            // Smooth scroll to top of table
            $('html, body').animate({
                scrollTop: $('.task-table-wrapper').offset().top - 80
            }, 200);
        }
    });

    // Search filter input (debounced by 350ms)
    $('#filter_search').on('input', function () {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(function () {
            loadTasks(1);
        }, 350);
    });

    // Dropdown filters change
    $('#filter_location, #filter_group, #filter_user, #filter_status, #filter_per_page').on('change', function () {
        loadTasks(1);
    });

    // Reset filters button
    $('#btn_reset_filter').on('click', function (e) {
        e.preventDefault();
        $('#filter_search').val('');
        $('#filter_location').val('');
        $('#filter_group').val('');
        $('#filter_user').val('');
        $('#filter_status').val('pending_in_progress');
        $('#filter_per_page').val('10');
        loadTasks(1);
    });

    // Export to Excel (.xlsx) with all active filters
    $('#btn_export_excel').on('click', function (e) {
        e.preventDefault();
        let params = getFilterData(1);
        delete params.page;
        params.format = 'xlsx';
        window.location.href = "{{ route('tasks.export') }}?" + $.param(params);
    });

    // Export to CSV with all active filters
    $('#btn_export_csv').on('click', function (e) {
        e.preventDefault();
        let params = getFilterData(1);
        delete params.page;
        params.format = 'csv';
        window.location.href = "{{ route('tasks.export') }}?" + $.param(params);
    });

    // Print table
    $('#btn_print_table').on('click', function (e) {
        e.preventDefault();
        window.print();
    });

    // ================= STATUS CHANGE (IN-PLACE, PRESERVES PAGE) =================
    $(document).on('change', '.changeStatus', function () {
        let $select = $(this);
        let id = $select.data('id');
        let newStatus = $select.val();
        let $tr = $select.closest('tr');
        let activeFilterStatus = $('#filter_status').val();

        $select.prop('disabled', true);

        $.ajax({
            url: '/tasks/' + id + '/status',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                status: newStatus
            },
            success: function (res) {
                $select.prop('disabled', false);

                if (res && res.status === true) {
                    Toast.fire({
                        icon: 'success',
                        title: res.message || 'Status updated successfully'
                    });

                    // Check if row should still be visible under active filter
                    let shouldRemove = false;
                    if (!activeFilterStatus || activeFilterStatus === 'pending_in_progress') {
                        if (newStatus === 'completed') {
                            shouldRemove = true;
                        }
                    } else if (activeFilterStatus === 'pending') {
                        if (newStatus !== 'pending') shouldRemove = true;
                    } else if (activeFilterStatus === 'in_progress') {
                        if (newStatus !== 'in_progress') shouldRemove = true;
                    } else if (activeFilterStatus === 'completed') {
                        if (newStatus !== 'completed') shouldRemove = true;
                    }

                    if (shouldRemove) {
                        $tr.fadeOut(300, function () {
                            // Reload the SAME page to maintain counts and keep user on the exact page!
                            loadTasks(currentPage, false);
                        });
                    }
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: res.message || 'Something went wrong'
                    });
                }
            },
            error: function (xhr) {
                $select.prop('disabled', false);
                Toast.fire({
                    icon: 'error',
                    title: xhr.responseJSON?.message || 'Server error occurred'
                });
            }
        });
    });

    // ================= SUBTASK CHANGE (IN-PLACE) =================
    $(document).on('change', '.changeSubtask', function () {
        let $select = $(this);
        let id = $select.data('id');
        let subtaskId = $select.val();

        $select.prop('disabled', true);

        $.ajax({
            url: '/tasks/' + id + '/subtask',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                subtask_id: subtaskId
            },
            success: function (res) {
                $select.prop('disabled', false);
                if (res && res.status === true) {
                    Toast.fire({
                        icon: 'success',
                        title: res.message || 'Subtask updated successfully'
                    });
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: res.message || 'Something went wrong'
                    });
                }
            },
            error: function (xhr) {
                $select.prop('disabled', false);
                Toast.fire({
                    icon: 'error',
                    title: xhr.responseJSON?.message || 'Server error occurred'
                });
            }
        });
    });

    // ================= REMARK MODAL & SUBMIT =================
    $(document).on('click', '.remarkBtn', function (e) {
        e.preventDefault();
        let id = $(this).data('id');
        let title = $(this).data('title');
        $('#remark_task_id').val(id);
        $('#remark_task_title').text(title || 'Task #' + id);
        $('#remark_text').val('');
        $('#remarkModal').modal('show');
    });

    $('#submitRemark').on('click', function () {
        let id = $('#remark_task_id').val();
        let remark = $('#remark_text').val().trim();

        if (remark === '') {
            Swal.fire('Error', 'Please enter remark description', 'warning');
            return;
        }

        let $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Submitting...');

        $.ajax({
            url: '/tasks/' + id + '/remark',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                remark: remark
            },
            success: function (res) {
                $btn.prop('disabled', false).html('<i class="fa fa-paper-plane me-1"></i> Submit Remark');
                if (res.status === true) {
                    $('#remarkModal').modal('hide');
                    Toast.fire({
                        icon: 'success',
                        title: res.message || 'Remark submitted successfully'
                    });
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: res.message || 'Something went wrong'
                    });
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false).html('<i class="fa fa-paper-plane me-1"></i> Submit Remark');
                Toast.fire({
                    icon: 'error',
                    title: xhr.responseJSON?.message || 'Server error occurred'
                });
            }
        });
    });

    // ================= DELETE CONFIRMATION =================
    $(document).on('click', '.delete-btn', function (e) {
        e.preventDefault();
        let form = $(this).closest('form');
        Swal.fire({
            title: 'Are you sure?',
            text: "This task will be deleted permanently!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

});
</script>

@endsection
