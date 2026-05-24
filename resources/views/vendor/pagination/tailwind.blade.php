@if ($paginator->hasPages())
    <nav class="flex items-center justify-center gap-1" role="navigation" aria-label="Pagination Navigation">
        @if ($paginator->onFirstPage())
            <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">&laquo;</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-2 text-sm text-blue-900 bg-white border border-gray-200 rounded-lg hover:bg-blue-50 transition-colors font-medium">
                &laquo;
            </a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-3 py-2 text-sm text-gray-400">...</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="px-3 py-2 text-sm text-white bg-blue-900 rounded-lg font-bold shadow-md">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="px-3 py-2 text-sm text-blue-900 bg-white border border-gray-200 rounded-lg hover:bg-blue-50 transition-colors font-medium">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-2 text-sm text-blue-900 bg-white border border-gray-200 rounded-lg hover:bg-blue-50 transition-colors font-medium">
                &raquo;
            </a>
        @else
            <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">&raquo;</span>
        @endif
    </nav>
@endif
