<section class="block">
    @include('partials.block-head', ['title' => $title ?: 'নির্বাচিত সংবাদ', 'category' => $category])
    <div class="grid-4">
        @foreach ($posts as $post)
            @include('partials.card', ['post' => $post])
        @endforeach
    </div>
</section>
