@php
    $settings = \App\Models\Setting::all_cached();
    $logo     = $settings['site_logo'] ?? null;

    /*
     * Client correction 1: the masthead colour has to be changeable without a
     * deploy, so the three bar colours come from settings and are written out
     * as CSS variables. Blank settings fall back to the stylesheet's own
     * values, which means an empty database behaves exactly as before.
     */
    $theme = array_filter([
        '--accent'     => $settings['color_accent']    ?? null,
        '--accent-dk'  => $settings['color_accent_dk'] ?? null,
        '--bar'        => $settings['color_header_bg'] ?? null,
        '--bar-2'      => $settings['color_nav_bg']    ?? null,
    ], fn ($v) => filled($v));

    // Built in the menu builder. If no menu has been set up yet, fall back to
    // the categories so the site is never left without navigation.
    $navItems = \App\Models\Menu::tree('header');

    if (empty($navItems)) {
        $navItems = collect([['label' => 'হোম', 'url' => route('home'), 'target' => null, 'children' => []]])
            ->concat(\App\Models\Category::inMenu()->get()->map(fn ($c) => [
                'label' => $c->name, 'url' => route('category.show', $c), 'target' => null, 'children' => [],
            ]))->all();
    }

    // Client corrections 6 and 8: a সাম্প্রতিক control in the header.
    $latestText = $settings['nav_latest_text'] ?? 'সাম্প্রতিক';
    $latestUrl  = ($settings['nav_latest_url'] ?? '') ?: route('latest');

    // Client correction 10: the label reads "Latest Video" and must stay in
    // English even when the page is translated.
    $ctaText = $settings['nav_cta_text'] ?? 'Latest Video';
    $ctaUrl  = ($settings['nav_cta_url'] ?? '') ?: route('video');

    $languages = [
        'bn' => 'বাংলা',
        'en' => 'English',
        'hi' => 'हिन्दी',
        'ar' => 'العربية',
        'ur' => 'اردو',
    ];
@endphp

@if ($theme)
    <style>:root{@foreach ($theme as $var => $value){{ $var }}:{{ $value }};@endforeach}</style>
@endif

<div class="topbar">
    <div class="wrap">
        <span class="clock">
            <span>{{ now()->format('F j, Y') }}</span>
            <span id="live-clock">{{ now()->format('h:i:s A') }}</span>
        </span>

        {{-- Correction 15: weather and the stock index, top right. --}}
        <div class="topbar-right">
            @include('partials.topbar-widgets')
            @include('partials.social-row')
        </div>
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
                {{-- Client correction 2 wants the logo here. Until one is
                     uploaded under Site settings → Branding the name is shown
                     as text, because an empty masthead is worse than either. --}}
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

        {{-- Client correction 11 fixes this order:
             সাম্প্রতিক → Latest Video → search → language. --}}
        <div class="nav-actions">
            @if ($latestText !== '')
                <a class="nav-latest" href="{{ $latestUrl }}">
                    <span class="nav-latest-dot" aria-hidden="true"></span>{{ $latestText }}
                </a>
            @endif

            @if ($ctaText !== '')
                {{-- translate="no" keeps Google's widget off this label. --}}
                <a class="nav-cta notranslate" translate="no" href="{{ $ctaUrl }}">{{ $ctaText }}</a>
            @endif

            <button class="nav-search" type="button" data-search-toggle aria-label="Search">&#9906;</button>

            {{-- Client correction 19: a proper dropdown rather than a bare
                 select. Google's own gadget stays mounted off-screen and is
                 driven from here — it cannot be styled to match anything. --}}
            <div class="lang-switch" translate="no">
                <button class="lang-btn" type="button" data-lang-toggle
                        aria-expanded="false" aria-haspopup="true" aria-label="ভাষা নির্বাচন করুন">
                    <svg class="lang-globe" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.7"/>
                        <ellipse cx="12" cy="12" rx="4" ry="9" fill="none" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M3.3 9h17.4M3.3 15h17.4" fill="none" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                    <span class="lang-current" data-lang-label>{{ $languages['bn'] }}</span>
                    <span class="lang-caret" aria-hidden="true"></span>
                </button>

                <ul class="lang-menu" data-lang-menu hidden>
                    @foreach ($languages as $code => $name)
                        <li>
                            <button type="button" data-lang="{{ $code }}" lang="{{ $code }}">
                                <span class="lang-tick" aria-hidden="true">&#10003;</span>{{ $name }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
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
    var NAMES  = @json($languages);

    // Written without optional chaining or arrow functions on purpose — a
    // good share of readers are on older Android webviews.
    var root = document.querySelector('.lang-switch');
    if (!root) return;

    var toggle = root.querySelector('[data-lang-toggle]');
    var menu   = root.querySelector('[data-lang-menu]');
    var label  = root.querySelector('[data-lang-label]');

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

    function markCurrent(lang) {
        label.textContent = NAMES[lang] || NAMES[SOURCE];

        menu.querySelectorAll('[data-lang]').forEach(function (btn) {
            btn.classList.toggle('is-current', btn.getAttribute('data-lang') === lang);
        });
    }

    function closeMenu() {
        menu.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
    }

    function openMenu() {
        menu.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
    }

    toggle.addEventListener('click', function (e) {
        e.stopPropagation();
        menu.hidden ? openMenu() : closeMenu();
    });

    menu.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-lang]');
        if (!btn) return;

        var lang = btn.getAttribute('data-lang');
        closeMenu();

        if (lang === SOURCE) {
            restoreOriginal();
            return;
        }

        markCurrent(lang);
        translateTo(lang);
    });

    // Click-away and Escape, so the panel never gets stranded open.
    document.addEventListener('click', function (e) {
        if (!root.contains(e.target)) closeMenu();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeMenu();
    });

    markCurrent(currentLanguage());
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
