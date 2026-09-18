@php
    $ad = \App\Models\Ad::live($placement)->first();

    $src = $ad && $ad->image
        // http(s) URL or a path already under /public stays as-is;
        // anything else is an admin upload living on the storage disk.
        ? (\Illuminate\Support\Str::startsWith($ad->image, ['http://', 'https://', '/'])
            ? $ad->image
            : asset('storage/'.$ad->image))
        : null;
@endphp

@if ($ad)
    <div class="ad">
        @if ($src)
            <a href="{{ $ad->link ?: '#' }}" rel="noopener sponsored">
                <img src="{{ $src }}" alt="{{ $ad->name }}" loading="lazy">
            </a>
        @else
            {!! $ad->code !!}
        @endif
    </div>
@endif
