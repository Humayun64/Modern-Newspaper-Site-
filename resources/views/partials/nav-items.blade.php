@foreach ($items as $item)
    <li class="{{ !empty($item['children']) ? 'has-sub' : '' }}">
        <a href="{{ $item['url'] }}"
           @if ($item['target']) target="{{ $item['target'] }}" rel="noopener" @endif
           class="{{ url()->current() === $item['url'] ? 'is-active' : '' }}">
            {{ $item['label'] }}
        </a>

        @if (!empty($item['children']))
            <ul class="nav-sub">
                @include('partials.nav-items', ['items' => $item['children']])
            </ul>
        @endif
    </li>
@endforeach
