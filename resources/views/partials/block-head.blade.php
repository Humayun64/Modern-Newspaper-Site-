@if (!empty($title))
    <div class="block-head">
        <h2 class="block-title">{{ $title }}</h2>
        @if (!empty($category))
            <a href="{{ route('category.show', $category) }}" class="block-more">আরও দেখুন</a>
        @endif
    </div>
@endif
