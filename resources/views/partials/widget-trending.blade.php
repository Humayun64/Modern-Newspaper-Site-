@if (($trending ?? collect())->isNotEmpty())
    <section class="block">
        @include('partials.block-head', ['title' => 'ট্রেন্ডিং'])
        @foreach ($trending as $i => $item)
            <article class="rank-card">
                <a href="{{ route('post.show', $item->slug) }}" class="thumb">
                    <img src="{{ $item->thumb }}" alt="{{ $item->title }}" loading="lazy">
                </a>
                <span class="rank-num" aria-hidden="true">{{ \App\Support\Bangla::digits($i + 1) }}</span>
                <div>
                    @if ($item->category)
                        <span class="badge">{{ $item->category->name }}</span>
                    @endif
                    <h3 class="card-title"><a href="{{ route('post.show', $item->slug) }}">{{ $item->title }}</a></h3>
                    <div class="meta"><span>{{ \App\Support\Bangla::ago($item->published_at) }}</span></div>
                </div>
            </article>
        @endforeach
    </section>
@endif
