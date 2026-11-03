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
                {{-- Client correction 2 wants the logo here. Until one is
                     uploaded under Site settings → Branding the name is shown
                     as text, because an empty masthead is worse than either. --}}
                <h1 class="brand-name"><a href="{{ route('home') }}">{{ $settings['site_name'] ?? 'amarDesh24.news' }}</a></h1>
                <p class="brand-tag">{{ $settings['site_tagline'] ?? '' }}</p>
            @endif
        </div>

        {{-- Correction 16: the masthead is logo on the left, search on the
             right. It was a small icon tucked into the menu bar, which is
             easy to miss; this is the shape readers expect.

             It is a plain GET form, so it works with JavaScript off and the
             browser's own history and autocomplete apply. --}}
        <form class="masthead-search" action="{{ route('search') }}" method="get" role="search">
            <input type="search" name="q" value="{{ request('q') }}"
                   placeholder="খবর খুঁজুন…" aria-label="সার্চ">
            <button type="submit" aria-label="খুঁজুন">
                <svg viewBox="0 0 24 24" width="17" height="17" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/>
                    <path d="M16.5 16.5 L21 21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </form>

        {{-- Corrections 3 and 5: no advertising on the site for now. The
             Ads module and this slot both stay — switching it back on is
             Site settings → Appearance → "Show the masthead banner", so
             nothing has to be rebuilt when the client sells one. --}}
        @if (($settings['show_header_ad'] ?? '0') === '1')
            @include('partials.ad', ['placement' => 'header'])
        @endif
    </div>
</header>

<nav class="nav" aria-label="প্রধান মেনু">
    <div class="wrap">
        <button class="nav-toggle" type="button" data-menu-toggle aria-expanded="false" aria-label="Menu">&#9776;</button>

        <ul class="nav-links" id="primary-menu">
            @include('partials.nav-items', ['items' => $navItems])
        </ul>

        {{-- Client correction 11 fixed this order: সাম্প্রতিক → Latest
             Video → search → language. Search has since moved up into the
             masthead, so what is left here is the first two and the
             language switcher, in the same order. --}}
        <div class="nav-actions">
            @if ($latestText !== '')
                <a class="nav-latest" href="{{ $latestUrl }}">
                    <span class="nav-latest-dot" aria-hidden="true"></span>{{ $latestText }}
                </a>
            @endif

            @if ($ctaText !== '')
                {{-- Correction 10, as the client now wants it: this label IS
                     translated along with the rest of the page. The written
                     note asked for the opposite, so the guard that held it in
                     English is removed rather than merely switched off — to
                     go back, put translate="no" and class="notranslate" on
                     this link and nothing else is needed.

                     The language switcher below keeps its own translate="no":
                     "বাংলা" and "English" are the names of the languages and
                     must not be translated into each other. --}}
                <a class="nav-cta" href="{{ $ctaUrl }}">{{ $ctaText }}</a>
            @endif

            {{-- Client correction 19: a proper dropdown rather than a bare
                 select. Google's own gadget stays mounted off-screen and is
                 driven from here — it cannot be styled to match anything. --}}
            <div class="lang-switch" translate="no">
                <button class="lang-btn" type="button" data-lang-toggle
                        aria-expanded="false" aria-haspopup="true" aria-label="ভাষা নির্বাচন করুন">
                    {{-- Correction 17: the word, not the globe. A globe on
                         its own does not say what the button does. --}}
                    <span class="lang-word">Translate</span>
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

{{-- Correction: the magnifier in the menu bar is gone. The search box
     in the masthead does the same job in plain sight, and two searches on
     one page is one too many. The drop-down bar it opened went with it. --}}

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
