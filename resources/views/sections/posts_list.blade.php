<section class="block">
    @include('partials.block-head', ['title' => $title, 'category' => $category])
    <div class="grid-2" style="gap:0 var(--gap)">
        @foreach ($posts as $post)
            @include('partials.row-card', ['post' => $post])
        @endforeach
    </div>
</section>
