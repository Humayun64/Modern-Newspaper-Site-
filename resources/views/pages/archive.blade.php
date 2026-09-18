@extends('layouts.app')

@section('title', $heading)
@section('meta_description', $description ?? '')

@section('content')
<div class="body-row">
    <div>
        <section class="block">
            @include('partials.block-head', ['title' => $heading])

            @if ($posts->isEmpty())
                <p style="color:var(--ink-soft)">{{ $emptyText ?? 'এখানে এখনও কোনো খবর প্রকাশ করা হয়নি।' }}</p>
            @else
                <div class="grid-3">
                    @foreach ($posts as $post)
                        @include('partials.card', ['post' => $post, 'showExcerpt' => true])
                    @endforeach
                </div>

                {{ $posts->withQueryString()->links('partials.pager') }}
            @endif
        </section>
    </div>

    <aside class="sidebar">
        @include('partials.ad', ['placement' => 'sidebar_top'])
        @include('partials.widget-author')
        @include('partials.widget-connect')
        @include('partials.widget-tags')
    </aside>
</div>
@endsection
