@php
    $siteSettings = \App\Models\Setting::all_cached();
    $indexable    = config('site.indexable');
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', $siteSettings['site_name'] ?? 'amarDesh24.news')</title>
    <meta name="description" content="@yield('meta_description', $siteSettings['meta_description'] ?? '')">

    @unless ($indexable)
        {{-- Staging copy: kept out of search results so it cannot compete
             with the live domain for the same headlines. --}}
        <meta name="robots" content="noindex, nofollow">
    @endunless

    <meta property="og:site_name" content="{{ $siteSettings['site_name'] ?? 'amarDesh24.news' }}">
    <meta property="og:title" content="@yield('title', $siteSettings['site_name'] ?? 'amarDesh24.news')">
    <meta property="og:description" content="@yield('meta_description', $siteSettings['meta_description'] ?? '')">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="bn_BD">
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
    @endif
    <meta name="twitter:card" content="summary_large_image">

    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Feed discovery: how readers and aggregators find the RSS --}}
    <link rel="alternate" type="application/rss+xml"
          title="{{ $siteSettings['site_name'] ?? 'amarDesh24.news' }}" href="{{ route('rss') }}">

    @if ($siteSettings['site_favicon'] ?? null)
        <link rel="icon" href="{{ \Illuminate\Support\Str::startsWith($siteSettings['site_favicon'], 'http') ? $siteSettings['site_favicon'] : asset('storage/'.$siteSettings['site_favicon']) }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Noto+Serif+Bengali:wght@500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/site.css') }}">

    @include('partials.schema')

    {{-- Analytics follows the same switch as indexing, so traffic from the
         staging copy never lands in the numbers you make decisions from. --}}
    @php $ga = $siteSettings['google_analytics'] ?? null; @endphp
    @if ($ga && $indexable)
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $ga }}');
        </script>
    @endif

    @stack('head')
</head>
<body>

@unless ($indexable)
    <div style="background:#fab219;color:#1c1d20;text-align:center;padding:7px 14px;font-size:13px;font-weight:700">
        প্রিভিউ সংস্করণ — এটি পরীক্ষামূলক সাইট, মূল সাইট নয়।
    </div>
@endunless

@include('partials.header')
@include('partials.ticker')

<main class="page">
    <div class="wrap">
        @yield('content')
    </div>
</main>

@include('partials.footer')

<script>
    // live clock in the top bar
    (function () {
        var el = document.getElementById('live-clock');
        if (!el) return;
        setInterval(function () {
            el.textContent = new Date().toLocaleTimeString('en-US', {
                hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
            });
        }, 1000);
    })();

    // mobile menu
    document.querySelector('[data-menu-toggle]')?.addEventListener('click', function () {
        var menu = document.getElementById('primary-menu');
        this.setAttribute('aria-expanded', String(menu.classList.toggle('is-open')));
    });

    // search bar
    document.querySelector('[data-search-toggle]')?.addEventListener('click', function () {
        var bar = document.getElementById('site-search');
        bar.classList.toggle('is-open');
        if (bar.classList.contains('is-open')) bar.querySelector('input')?.focus();
    });

    // latest / popular tabs
    document.querySelectorAll('.tabs').forEach(function (tabs) {
        tabs.addEventListener('click', function (e) {
            var btn = e.target.closest('.tab-btn');
            if (!btn) return;
            tabs.querySelectorAll('.tab-btn').forEach(function (b) {
                b.setAttribute('aria-selected', String(b === btn));
                var panel = document.getElementById(b.dataset.panel);
                if (panel) panel.hidden = (b !== btn);
            });
        });
    });

    // slider arrows
    document.querySelectorAll('[data-slider-prev], [data-slider-next]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id    = btn.dataset.sliderPrev || btn.dataset.sliderNext;
            var track = document.getElementById(id);
            if (!track) return;
            track.scrollBy({ left: btn.dataset.sliderNext ? track.clientWidth : -track.clientWidth, behavior: 'smooth' });
        });
    });

    // copy link
    document.querySelector('[data-copy]')?.addEventListener('click', function () {
        var box = this.closest('.share');
        var url = box?.dataset.shareUrl || window.location.href;
        var label = this.querySelector('.share-text');
        var original = label ? label.textContent : '';

        function done() {
            if (!label) return;
            label.textContent = 'কপি হয়েছে';
            setTimeout(function () { label.textContent = original; }, 1800);
        }

        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(done, function () {});
        } else {
            // Older mobile browsers, which are still common here.
            var tmp = document.createElement('input');
            tmp.value = url;
            document.body.appendChild(tmp);
            tmp.select();
            try { document.execCommand('copy'); done(); } catch (e) {}
            document.body.removeChild(tmp);
        }
    });
</script>
@stack('scripts')
</body>
</html>
