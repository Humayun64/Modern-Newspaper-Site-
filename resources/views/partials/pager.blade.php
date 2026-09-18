{{--
    Plain pagination markup. Laravel's default paginator view is Tailwind-based,
    and this site has no Tailwind, so it would render unstyled without this.
--}}
@if ($paginator->hasPages())
    <nav aria-label="পাতা" >
        <ul class="pagination">
            @if ($paginator->onFirstPage())
                <li class="disabled" aria-disabled="true"><span>&lsaquo;</span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev">&lsaquo;</a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="disabled"><span>{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="active" aria-current="page"><span>{{ \App\Support\Bangla::digits($page) }}</span></li>
                        @else
                            <li><a href="{{ $url }}">{{ \App\Support\Bangla::digits($page) }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">&rsaquo;</a></li>
            @else
                <li class="disabled" aria-disabled="true"><span>&rsaquo;</span></li>
            @endif
        </ul>
    </nav>
@endif
