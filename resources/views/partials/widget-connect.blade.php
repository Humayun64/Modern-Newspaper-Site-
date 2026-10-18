@php
    $s = \App\Models\Setting::all_cached();

    /*
     * Same rule as the small icon row: a network appears only when it has a
     * link. The whole card goes when none of them do — an empty "আমাদের সাথে
     * থাকুন" box is worse than no box.
     */
    $networks = collect([
        ['key' => 'facebook_url',  'class' => 'c-fb', 'label' => 'Facebook',  'icon' => 'f'],
        ['key' => 'twitter_url',   'class' => 'c-x',  'label' => 'Twitter',   'icon' => 'X'],
        ['key' => 'linkedin_url',  'class' => 'c-in', 'label' => 'LinkedIn',  'icon' => 'in'],
        ['key' => 'vk_url',        'class' => 'c-vk', 'label' => 'VK',        'icon' => '●'],
        ['key' => 'youtube_url',   'class' => 'c-yt', 'label' => 'YouTube',   'icon' => '►'],
        ['key' => 'instagram_url', 'class' => 'c-ig', 'label' => 'Instagram', 'icon' => '◉'],
    ])->map(fn ($n) => $n + ['url' => trim((string) ($s[$n['key']] ?? ''))])
      ->filter(fn ($n) => $n['url'] !== '')
      ->values();
@endphp

@if ($networks->isNotEmpty())
    <section class="block">
        @include('partials.block-head', ['title' => 'আমাদের সাথে থাকুন', 'category' => null])

        {{-- One link should not sit in a three-across grid looking lost. --}}
        <div class="connect-grid connect-{{ min($networks->count(), 3) }}">
            @foreach ($networks as $n)
                <a class="{{ $n['class'] }}" href="{{ $n['url'] }}" target="_blank" rel="noopener">
                    <span class="ico">{{ $n['icon'] }}</span>{{ $n['label'] }}
                </a>
            @endforeach
        </div>
    </section>
@endif
