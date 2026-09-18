{{-- Category badges laid over the bottom-left of a thumbnail. --}}
@php
    $badges = collect([$post->category])->filter()
        ->merge($post->relationLoaded('categories') ? $post->categories : [])
        ->unique('id')
        ->take(2);
@endphp

@if ($badges->isNotEmpty())
    <span class="thumb-badges">
        @foreach ($badges as $cat)
            <span class="badge">{{ $cat->name }}</span>
        @endforeach
    </span>
@endif
