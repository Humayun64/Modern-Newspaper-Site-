<section class="block">
    @include('partials.block-head', ['title' => $title ?: 'ট্রেন্ডিং', 'category' => $category])

    {{-- Numbered because this genuinely is a ranking, 1 to 5. --}}
    @foreach ($posts as $i => $post)
        <article class="rank-card">
            <a href="{{ route('post.show', $post->slug) }}" class="thumb">
                <img src="{{ $post->thumb }}" alt="{{ $post->title }}" loading="lazy">
            </a>
            <span class="rank-num" aria-hidden="true">{{ \App\Support\Bangla::digits($i + 1) }}</span>
            <div>
                @if ($post->category)
                    <span class="badge">{{ $post->category->name }}</span>
                @endif
                <h3 class="card-title"><a href="{{ route('post.show', $post->slug) }}">{{ $post->title }}</a></h3>
                <div class="meta"><span>{{ \App\Support\Bangla::ago($post->published_at) }}</span></div>
            </div>
        </article>
    @endforeach
</section>
