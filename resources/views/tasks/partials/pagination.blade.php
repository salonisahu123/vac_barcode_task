<div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-2">
    <div class="text-muted small mb-2 mb-md-0" style="font-size:13px;">
        @if($tasks->total() > 0)
            Showing <span class="font-weight-bold text-dark">{{ $tasks->firstItem() }}</span>
            to <span class="font-weight-bold text-dark">{{ $tasks->lastItem() }}</span>
            of <span class="font-weight-bold text-dark">{{ $tasks->total() }}</span> tasks
        @else
            Showing 0 to 0 of 0 tasks
        @endif
    </div>

    @if($tasks->hasPages())
    <div>
        <nav aria-label="Tasks pagination">
            <ul class="pagination pagination-sm mb-0" style="gap:2px;">
                {{-- Previous Page Link --}}
                @if ($tasks->onFirstPage())
                    <li class="page-item disabled"><span class="page-link" style="border-radius:6px;">&laquo;</span></li>
                @else
                    <li class="page-item">
                        <a class="page-link task-page-link" href="#" data-page="{{ $tasks->currentPage() - 1 }}" rel="prev" style="border-radius:6px;">&laquo;</a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @php
                    $currentPage = $tasks->currentPage();
                    $lastPage = $tasks->lastPage();
                    $start = max(1, $currentPage - 2);
                    $end = min($lastPage, $currentPage + 2);
                @endphp

                @if($start > 1)
                    <li class="page-item"><a class="page-link task-page-link" href="#" data-page="1" style="border-radius:6px;">1</a></li>
                    @if($start > 2)
                        <li class="page-item disabled"><span class="page-link" style="border-radius:6px;">...</span></li>
                    @endif
                @endif

                @for ($page = $start; $page <= $end; $page++)
                    @if ($page == $currentPage)
                        <li class="page-item active" aria-current="page"><span class="page-link" style="border-radius:6px; background:#0d6efd; border-color:#0d6efd;">{{ $page }}</span></li>
                    @else
                        <li class="page-item"><a class="page-link task-page-link" href="#" data-page="{{ $page }}" style="border-radius:6px;">{{ $page }}</a></li>
                    @endif
                @endfor

                @if($end < $lastPage)
                    @if($end < $lastPage - 1)
                        <li class="page-item disabled"><span class="page-link" style="border-radius:6px;">...</span></li>
                    @endif
                    <li class="page-item"><a class="page-link task-page-link" href="#" data-page="{{ $lastPage }}" style="border-radius:6px;">{{ $lastPage }}</a></li>
                @endif

                {{-- Next Page Link --}}
                @if ($tasks->hasMorePages())
                    <li class="page-item">
                        <a class="page-link task-page-link" href="#" data-page="{{ $tasks->currentPage() + 1 }}" rel="next" style="border-radius:6px;">&raquo;</a>
                    </li>
                @else
                    <li class="page-item disabled"><span class="page-link" style="border-radius:6px;">&raquo;</span></li>
                @endif
            </ul>
        </nav>
    </div>
    @endif
</div>
