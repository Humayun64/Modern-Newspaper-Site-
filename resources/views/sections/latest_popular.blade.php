{{--
    The "জনপ্রিয়" tab was removed at the client's request. It ranked by view
    count, and with no view logging in place yet it had nothing honest to
    show — so the section is now a plain list of the newest articles.

    home.blade.php still passes $popular in. It is simply unused here, which
    means nothing else had to change and the tab can come back later by
    restoring this file.
--}}

<section class="block">
    @include('partials.block-head', ['title' => 'সর্বশেষ', 'category' => null])

    @foreach ($posts as $post)
        @include('partials.row-card', ['post' => $post])
    @endforeach
</section>
