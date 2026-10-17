@extends('layouts.app')

@section('content')

    @if ($hero->isNotEmpty())
        <div class="hero-row">
            @foreach ($hero as $section)
                @includeIf('sections.'.$section['layout'], [
                    'title'    => $section['title'],
                    'category' => $section['category'],
                    'posts'    => $section['posts'],
                    'popular'  => $popular,
                ])
            @endforeach
        </div>
    @endif

    <div class="body-row">
        <div class="stack">
            @foreach ($main as $section)
                @includeIf('sections.'.$section['layout'], [
                    'title'    => $section['title'],
                    'category' => $section['category'],
                    'posts'    => $section['posts'],
                ])
            @endforeach

            {{-- Correction 20: foreign-media highlights with a PDF to open.
                 These are not articles, so they do not come through
                 home_sections — the partial draws nothing when the list is
                 empty, which is how it stays out of the way until the first
                 one is uploaded. --}}
            @include('sections.global_coverage')
        </div>

        <aside class="sidebar">
            @include('partials.ad', ['placement' => 'sidebar_top'])

            {{-- Correction 15: the stock index and the weather, in the
                 sidebar where the client wants them. Each draws nothing
                 until it has something to show. --}}
            @include('partials.widget-stock')
            @include('partials.widget-weather')

            @include('partials.widget-author')

            @foreach ($sidebar as $section)
                @includeIf('sections.'.$section['layout'], [
                    'title'    => $section['title'],
                    'category' => $section['category'],
                    'posts'    => $section['posts'],
                ])
            @endforeach

            @include('partials.widget-connect')
            @include('partials.widget-tags')
        </aside>
    </div>

@endsection
