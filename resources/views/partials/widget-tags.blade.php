@php $tags = \App\Models\Tag::orderByDesc('posts_count')->limit(12)->get(); @endphp

@if ($tags->isNotEmpty())
    <section class="block">
        @include('partials.block-head', ['title' => 'জনপ্রিয় ট্যাগ'])
        <div class="chip-row">
            @foreach ($tags as $tag)
                <a class="chip" href="{{ route('tag.show', $tag) }}">{{ $tag->name }}</a>
            @endforeach
        </div>
    </section>
@endif
