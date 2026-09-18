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
                 off-screen and driven from here — its own gadget is an
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
     because Google will not build its control inside a hidden element. --}}
<div id="google_translate_element" class="gt-hidden" aria-hidden="true"></div>

@push('scripts')
<script>
(function () {
    var SOURCE = 'bn';
    var select = document.getElementById('lang-select');
    if (!select) return;

    /*
     * Google records the chosen language in TWO places, and both have to go
     * before the page will show the original again:
     *
     *   1. a googtrans cookie — set on a parent domain, not the host, so on
     *      new.amardesh24.news it lands on .amardesh24.news
     *   2. a #googtrans(bn|en) fragment appended to the URL, which Google
     *      re-reads on load and which overrides everything else
     *
     * Clearing only the cookie, on only the host, is why switching back to
     * Bangla kept snapping to English.
     */
    function currentLanguage() {
        var hash = location.hash.match(/#googtrans\(([^|)]+)\|([^)]+)\)/);
        if (hash) {
            return hash[2];
        }

        var cookie = document.cookie.match(/(?:^|;\s*)googtrans=\/[^\/]*\/([^;]+)/);
        return cookie ? decodeURIComponent(cookie[1]) : SOURCE;
    }

    function clearGoogleCookie() {
        var host  = location.hostname;
        var parts = host.split('.');
        var domains = ['', host];

        // .new.amardesh24.news, .amardesh24.news, .news — Google may have
        // used any of them, and a cookie only clears on the domain that set it.
        for (var i = 0; i < parts.length - 1; i++) {
            domains.push('.' + parts.slice(i).join('.'));
        }

        var paths = ['/', location.pathname];

        domains.forEach(function (domain) {
            paths.forEach(function (path) {
                document.cookie = 'googtrans=;expires=Thu, 01 Jan 1970 00:00:01 GMT;path=' + path
                    + (domain ? ';domain=' + domain : '');
            });
        });
    }

    function restoreOriginal() {
        clearGoogleCookie();

        // Reload WITHOUT the fragment. A plain location.reload() keeps it,
        // and Google would translate straight back.
        location.replace(location.pathname + location.search);
    }

    function translateTo(lang) {
        var attempts = 0;

        (function apply() {
            var combo = document.querySelector('.goog-te-combo');

            if (combo) {
                combo.value = lang;
                combo.dispatchEvent(new Event('change'));
                return;
            }

            // The widget script is deferred, so a click in the first moment
            // after load can arrive before the control exists.
            if (++attempts <= 40) {
                setTimeout(apply, 150);
            }
        })();
    }

    select.value = currentLanguage();

    select.addEventListener('change', function () {
        var lang = this.value;

        if (lang === SOURCE) {
            restoreOriginal();
            return;
        }

        translateTo(lang);
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
