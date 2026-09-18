@php $lead = $posts->first(); $rest = $posts->skip(1); @endphp

<section class="block">
    @include('partials.block-head', ['title' => $title, 'category' => $category])
    <div class="express">
        <div>
            @if ($lead)
                @include('partials.card', ['post' => $lead, 'showExcerpt' => true])
            @endif
        </div>
        <div>
            @foreach ($rest as $post)
                @include('partials.row-card', ['post' => $post])
            @endforeach
        </div>
    </div>
</section>
