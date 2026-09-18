@extends('layouts.app')

@section('title', $page->meta_title ?: $page->title)
@section('meta_description', $page->meta_description)

@push('head')
    <link rel="stylesheet" href="{{ \App\Support\Asset::versioned('css/single.css') }}">
@endpush

@section('content')
<div class="body-row">
    <article class="article">
        <h1 class="article-title">{{ $page->title }}</h1>
        <div class="article-body">{!! $page->body !!}</div>
    </article>

    <aside class="sidebar">
        @include('partials.widget-search')
        @include('partials.ad', ['placement' => 'sidebar_top'])
        @include('partials.widget-categories')
        @include('partials.widget-connect')
    </aside>
</div>
@endsection
