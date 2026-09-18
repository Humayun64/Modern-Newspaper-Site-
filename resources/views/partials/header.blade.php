@php
    $settings = \App\Models\Setting::all_cached();
    $logo     = $settings['site_logo'] ?? null;

    // Built in the menu builder. If no menu has been set up yet, fall back to
    // the categories so the site is never left without navigation.
    $navItems = \App\Models\Menu::tree('header');

    if (empty($navItems)) {
        $navItems = collect([['label' => 'হোম', 'url' => route('home'), 'target' => null, 'children' => []]])
            ->concat(\App\Models\Category::inMenu()->get()->map(fn ($c) => [
                'label' => $c->name, 'url' => route('category.show', $c), 'target' => null, 'children' => [],
            ]))->all();
    }
@endphp

<div class="topbar">
    <div class="wrap">
        <span class="clock">
            <span>{{ now()->format('F j, Y') }}</span>
            <span id="live-clock">{{ now()->format('h:i:s A') }}</span>
        </span>
        @include('partials.social-row')
    </div>
</div>

<header class="masthead">
    <div class="wrap">
        <div class="brand">
            @if ($logo)
                <a href="{{ route('home') }}">
                    <img class="brand-logo"
                         src="{{ \Illuminate\Support\Str::startsWith($logo, 'http') ? $logo : asset('storage/'.$logo) }}"
                         alt="{{ $settings['site_name'] ?? 'amarDesh24.news' }}">
                </a>
            @else
                <h1 class="brand-name"><a href="{{ route('home') }}">{{ $settings['site_name'] ?? 'amarDesh24.news' }}</a></h1>
                <p class="brand-tag">{{ $settings['site_tagline'] ?? '' }}</p>
            @endif
        </div>

        @include('partials.ad', ['placement' => 'header'])
    </div>
</header>

<nav class="nav" aria-label="প্রধান মেনু">
    <div class="wrap">
        <button class="nav-toggle" type="button" data-menu-toggle aria-expanded="false" aria-label="Menu">&#9776;</button>

        <ul class="nav-links" id="primary-menu">
            @include('partials.nav-items', ['items' => $navItems])
        </ul>

        <div class="nav-actions">
            <button class="nav-search" type="button" data-search-toggle aria-label="Search">&#9906;</button>
            @if (($settings['nav_cta_text'] ?? 'Watch Videos') !== '')
                <a class="nav-cta" href="{{ ($settings['nav_cta_url'] ?? '') ?: route('home') }}">
                    {{ $settings['nav_cta_text'] ?? 'Watch Videos' }}
                </a>
            @endif
        </div>
    </div>
</nav>

<div class="search-bar" id="site-search">
    <div class="wrap">
        <form action="{{ route('search') }}" method="get" role="search">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="যা খুঁজছেন লিখুন…" aria-label="Search">
            <button type="submit">খুঁজুন</button>
        </form>
    </div>
</div>
