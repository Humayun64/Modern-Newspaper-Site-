@php $lead = $posts->first(); $rest = $posts->skip(1); @endphp

<section class="block">
    @include('partials.block-head', ['title' => $title, 'category' => $category])

    @if ($lead)
        @include('partials.card', ['post' => $lead])
    @endif

    @if ($rest->isNotEmpty())
        <div style="margin-top:14px">
            @foreach ($rest as $post)
                @include('partials.row-card', ['post' => $post])
            @endforeach
        </div>
    @endif
</section>
