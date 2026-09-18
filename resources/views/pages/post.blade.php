@extends('layouts.app')

@section('title', $post->meta_title ?: $post->title)
@section('meta_description', $post->meta_description ?: $post->excerpt)
@section('og_type', 'article')
@section('og_image', $post->thumb)

@push('head')
    <link rel="stylesheet" href="{{ \App\Support\Asset::versioned('css/single.css') }}">
@endpush

@section('content')

@include('partials.breadcrumb')

<div class="body-row">

    <div class="stack">

        <article class="article">
            @if ($post->category)
                <a href="{{ route('category.show', $post->category) }}" class="badge badge-pill">
                    <span class="badge-dot" aria-hidden="true"></span>{{ $post->category->name }}
                </a>
            @endif

            <h1 class="article-title">{{ $post->title }}</h1>

            @if ($post->excerpt)
                <p class="article-standfirst">{{ $post->excerpt }}</p>
            @endif

            <div class="article-meta">
                @if ($post->author)
                    <span class="am-author">
                        <img src="{{ $post->author->photo ? asset('storage/'.$post->author->photo) : 'https://ui-avatars.com/api/?name='.urlencode($post->author->name).'&size=48&background=d32b2b&color=fff' }}" alt="">
                        {{ $post->author->display_name }}
                    </span>
                @endif
                <span>{{ \App\Support\Bangla::date($post->published_at) }}</span>
                @if ($post->reading_time)
                    <span>{{ \App\Support\Bangla::digits($post->reading_time) }} মিনিট পড়ুন</span>
                @endif
                <span>{{ \App\Support\Bangla::number($post->views) }} পঠিত</span>
            </div>

            @if ($post->is_sponsored)
                <p class="sponsored-note">স্পন্সরড কনটেন্ট</p>
            @endif

            @if ($post->featured_image)
                <figure class="article-figure">
                    <img src="{{ $post->thumb }}" alt="{{ $post->title }}">
                    @if ($post->image_caption || $post->image_credit)
                        <figcaption>{{ $post->image_caption }}@if($post->image_credit) — {{ $post->image_credit }}@endif</figcaption>
                    @endif
                </figure>
            @endif

            {{-- Share above the article as well as below: most sharing happens
                 on the strength of the headline, before the story is read. --}}
            @include('partials.share')

            @if ($post->type === 'video' && $post->video_url)
                <p><a class="chip" href="{{ $post->video_url }}" rel="noopener">ভিডিওটি দেখুন</a></p>
            @endif

            <div class="article-body">{!! $post->body !!}</div>

            @include('partials.share')

            @if ($post->tags->isNotEmpty())
                <div class="chip-row article-tags">
                    @foreach ($post->tags as $tag)
                        <a class="chip" href="{{ route('tag.show', $tag) }}">{{ $tag->name }}</a>
                    @endforeach
                </div>
            @endif

            @if ($post->author)
                <section class="author-box">
                    <h2 class="box-label">লেখক সম্পর্কে</h2>
                    <div class="author-box-inner">
                        <img class="author-box-avatar"
                             src="{{ $post->author->photo ? asset('storage/'.$post->author->photo) : 'https://ui-avatars.com/api/?name='.urlencode($post->author->name).'&size=160&background=d32b2b&color=fff' }}"
                             alt="{{ $post->author->display_name }}">
                        <div>
                            <p class="author-box-name">{{ $post->author->display_name }}</p>
                            @if ($post->author->designation)
                                <p class="author-box-role">{{ $post->author->designation }}</p>
                            @endif
                            @if ($post->author->bio)
                                <p class="author-box-bio">{{ $post->author->bio }}</p>
                            @endif
                            <a class="chip" href="{{ route('search', ['q' => $post->author->name]) }}">সব লেখা দেখুন</a>
                        </div>
                    </div>
                </section>
            @endif

            @if ($previous || $next)
                <nav class="post-nav" aria-label="আরও খবর">
                    <div>
                        @if ($previous)
                            <span class="post-nav-label">পূর্ববর্তী</span>
                            <a href="{{ route('post.show', $previous->slug) }}">{{ $previous->title }}</a>
                        @endif
                    </div>
                    <div class="post-nav-next">
                        @if ($next)
                            <span class="post-nav-label">পরবর্তী</span>
                            <a href="{{ route('post.show', $next->slug) }}">{{ $next->title }}</a>
                        @endif
                    </div>
                </nav>
            @endif
        </article>

        @if ($related->isNotEmpty())
            <section class="block">
                @include('partials.block-head', ['title' => 'সম্পর্কিত খবর', 'category' => $post->category])
                <div class="grid-3">
                    @foreach ($related as $item)
                        @include('partials.card', ['post' => $item])
                    @endforeach
                </div>
            </section>
        @endif

        @if ($missed->isNotEmpty())
            <section class="block">
                @include('partials.block-head', ['title' => 'যা মিস করেছেন'])
                <div class="grid-4">
                    @foreach ($missed as $item)
                        @include('partials.card', ['post' => $item])
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    <aside class="sidebar">
        @include('partials.widget-search')
        @include('partials.ad', ['placement' => 'sidebar_top'])
        @include('partials.widget-trending')
        @include('partials.widget-categories')
        @include('partials.widget-connect')
    </aside>
</div>

@endsection
