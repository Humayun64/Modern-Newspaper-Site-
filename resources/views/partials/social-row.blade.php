@php
    $s = \App\Models\Setting::all_cached();

    /*
     * Only the networks that actually have a link.
     *
     * Every icon used to render whatever the settings said, falling back to
     * "#" — so clearing a link in the admin did nothing and the row still
     * advertised accounts that do not exist. Now the settings control it:
     * clear a URL under Site settings → Social links and that icon is gone.
     */
    $networks = collect([
        ['key' => 'facebook_url',  'class' => 's-fb', 'label' => 'Facebook',  'icon' => 'f'],
        ['key' => 'twitter_url',   'class' => 's-x',  'label' => 'X',         'icon' => 'X'],
        ['key' => 'youtube_url',   'class' => 's-yt', 'label' => 'YouTube',   'icon' => '►'],
        ['key' => 'instagram_url', 'class' => 's-ig', 'label' => 'Instagram', 'icon' => '●'],
    ])->map(fn ($n) => $n + ['url' => trim((string) ($s[$n['key']] ?? ''))])
      ->filter(fn ($n) => $n['url'] !== '');
@endphp

@if ($networks->isNotEmpty())
    <div class="social-row">
        @foreach ($networks as $n)
            <a class="{{ $n['class'] }}" href="{{ $n['url'] }}"
               aria-label="{{ $n['label'] }}" target="_blank" rel="noopener">{{ $n['icon'] }}</a>
        @endforeach
    </div>
@endif
