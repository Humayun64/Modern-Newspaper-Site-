@if (in_array($post->type, ['video', 'podcast', 'gallery', 'live']))
    <span class="media-flag" aria-hidden="true">@switch($post->type)
        @case('video')   &#9658; @break
        @case('podcast') &#9834; @break
        @case('gallery') &#9635; @break
        @case('live')    &#9679; @break
    @endswitch</span>
@endif
