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
        </div>

        <aside class="sidebar">
            @include('partials.ad', ['placement' => 'sidebar_top'])
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
