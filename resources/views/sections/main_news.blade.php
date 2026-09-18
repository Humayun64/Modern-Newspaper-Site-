@php $lead = $posts->first(); $rest = $posts->skip(1)->take(4); @endphp

<section class="block">
    @include('partials.block-head', ['title' => $title ?: 'প্রধান সংবাদ', 'category' => $category])

    @if ($lead)
        <div class="lead">
            <a href="{{ route('post.show', $lead->slug) }}" class="thumb">
                <img src="{{ $lead->thumb }}" alt="{{ $lead->title }}">
                @include('partials.media-flag', ['post' => $lead])
            </a>
            <div class="lead-body">
                @if ($lead->category)
                    <span class="badge">{{ $lead->category->name }}</span>
                @endif
                <h3 class="card-title"><a href="{{ route('post.show', $lead->slug) }}">{{ $lead->title }}</a></h3>
                <div class="meta">
                    @if ($lead->author)<span>{{ $lead->author->display_name }}</span>@endif
                    <span>{{ \App\Support\Bangla::ago($lead->published_at) }}</span>
                </div>
            </div>
        </div>
    @endif

    @if ($rest->isNotEmpty())
        <div style="margin-top:16px">
            @foreach ($rest as $post)
                @include('partials.row-card', ['post' => $post])
            @endforeach
        </div>
    @endif
</section>
