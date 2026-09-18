<section class="block">
    @include('partials.block-head', ['title' => $title, 'category' => $category])

    @foreach ($posts as $post)
        <article class="express" style="padding-block:16px;border-bottom:1px solid var(--rule)">
            <a href="{{ route('post.show', $post->slug) }}" class="thumb">
                <img src="{{ $post->thumb }}" alt="{{ $post->title }}" loading="lazy">
                @include('partials.media-flag')
                @include('partials.thumb-badges')
            </a>
            <div>
                <h3 class="card-title" style="font-size:19px;margin-top:0"><a href="{{ route('post.show', $post->slug) }}">{{ $post->title }}</a></h3>
                <div class="meta">
                    @if ($post->author)<span>{{ $post->author->display_name }}</span>@endif
                    <span>{{ \App\Support\Bangla::ago($post->published_at) }}</span>
                </div>
                <p style="font-size:14.5px;color:var(--ink-soft)">{{ \Illuminate\Support\Str::limit($post->excerpt, 150) }}</p>
                <a class="chip" href="{{ route('post.show', $post->slug) }}">বিস্তারিত পড়ুন</a>
            </div>
        </article>
    @endforeach
</section>
