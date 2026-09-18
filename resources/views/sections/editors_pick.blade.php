<section class="block">
    @include('partials.block-head', ['title' => $title ?: 'সম্পাদকের পছন্দ', 'category' => $category])
    <div class="stack" style="gap:14px">
        @foreach ($posts as $post)
            @include('partials.overlay-card', ['post' => $post])
        @endforeach
    </div>
</section>
