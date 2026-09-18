{{-- Home / 2026 / সেপ্টেম্বর / 17 / Headline --}}
<nav class="crumbs" aria-label="Breadcrumb">
    <a href="{{ route('home') }}">হোম</a>
    @if ($post->category)
        <span aria-hidden="true">/</span>
        <a href="{{ route('category.show', $post->category) }}">{{ $post->category->name }}</a>
    @endif
    @if ($post->published_at)
        <span aria-hidden="true">/</span>
        <span>{{ \App\Support\Bangla::date($post->published_at) }}</span>
    @endif
    <span aria-hidden="true">/</span>
    <span class="crumbs-current">{{ \Illuminate\Support\Str::limit($post->title, 46) }}</span>
</nav>
