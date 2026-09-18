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
            {{-- Our own select, styled to the bar. Google's widget is mounted
                 hidden below and driven from here — its default gadget is an
                 unstyled white box that cannot be made to match anything. --}}
            <label class="lang-switch">
                <span class="lang-globe" aria-hidden="true">&#127760;</span>
                <select id="lang-select" aria-label="ভাষা নির্বাচন করুন">
                    <option value="bn">বাংলা</option>
                    <option value="en">English</option>
                    <option value="hi">हिन्दी</option>
                    <option value="ar">العربية</option>
                    <option value="ur">اردو</option>
                </select>
            </label>

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

{{-- Mount point for Google's widget. Kept off-screen rather than display:none,
     because Google will not build the control inside a hidden element. --}}
<div id="google_translate_element" class="gt-hidden" aria-hidden="true"></div>

@push('scripts')
<script>
(function () {
    var select = document.getElementById('lang-select');
    if (!select) return;

    // Reflect whatever language the visitor is already reading in.
    var current = (document.cookie.match(/(?:^|;\s*)googtrans=\/[^\/]*\/([^;]+)/) || [])[1];
    if (current) {
        select.value = current;
    }

    function applyLanguage(lang) {
        // Returning to Bangla means removing the translation, not translating
        // into it. Clearing the cookie and reloading is the only reliable way.
        if (lang === 'bn') {
            var host = location.hostname;
            ['/', ''].forEach(function (path) {
                document.cookie = 'googtrans=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=' + (path || '/');
                document.cookie = 'googtrans=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/;domain=' + host;
                document.cookie = 'googtrans=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/;domain=.' + host;
            });
            location.reload();
            return;
        }

        var combo = document.querySelector('.goog-te-combo');

        // The script loads asynchronously, so a click straight after page load
        // can arrive before the control exists. Wait briefly rather than fail.
        if (!combo) {
            var tries = 0;
            var timer = setInterval(function () {
                combo = document.querySelector('.goog-te-combo');
                if (combo) {
                    clearInterval(timer);
                    combo.value = lang;
                    combo.dispatchEvent(new Event('change'));
                } else if (++tries > 30) {
                    clearInterval(timer);
                }
            }, 150);
            return;
        }

        combo.value = lang;
        combo.dispatchEvent(new Event('change'));
    }

    select.addEventListener('change', function () {
        applyLanguage(this.value);
    });
})();

function googleTranslateElementInit() {
    new google.translate.TranslateElement({
        pageLanguage: 'bn',
        includedLanguages: 'bn,en,hi,ar,ur',
        autoDisplay: false
    }, 'google_translate_element');
}
</script>
<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" defer></script>
@endpush
