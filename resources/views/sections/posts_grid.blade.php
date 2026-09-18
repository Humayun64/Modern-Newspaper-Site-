<section class="block">
    @include('partials.block-head', ['title' => $title, 'category' => $category])
    <div class="grid-3">
        @foreach ($posts as $post)
            @include('partials.card', ['post' => $post])
        @endforeach
    </div>
</section>
