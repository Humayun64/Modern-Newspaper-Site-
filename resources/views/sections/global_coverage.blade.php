@php
    $s = \App\Models\Setting::all_cached();

    /*
     * Correction 20: foreign-media highlights, the way the current site shows
     * them — a grid of page thumbnails that open the document.
     *
     * amardesh24.news/foreign-media-highlights is exactly that: scanned
     * pages, no headlines, no dates. So the cover leads here and the title
     * sits under it as a caption rather than as a headline. The title is
     * still required in the admin, because it is the alt text and the only
     * thing a screen reader or a search engine has to go on.
     *
     * Clicking opens the document in a panel over the page. A PDF goes in a
     * frame, a page scan is shown as the picture it is — and either way the
     * reader stays on the homepage instead of being thrown into a download.
     *
     * Plain arrays in the cache, never models: the database cache store
     * serialises what it is handed, and a serialised model comes back as an
     * incomplete object.
     */
    $heading = trim((string) ($s['coverage_heading'] ?? '')) ?: 'বিশ্ব গণমাধ্যমে';
    $note    = trim((string) ($s['coverage_note'] ?? '')) ?: 'আন্তর্জাতিক সংবাদমাধ্যমে বাংলাদেশ';

    $coverage = \Illuminate\Support\Facades\Cache::remember('home.global_coverage', 300, fn () =>
        \App\Models\GlobalCoverage::active()
            ->limit(8)
            ->get()
            ->map(fn ($c) => [
                'title'    => $c->title,
                'outlet'   => $c->outlet,
                'cover'    => $c->cover_url,
                'file'     => $c->file_url,
                'external' => $c->source_url,
                'badge'    => $c->badge,
                'kind'     => $c->is_pdf ? 'pdf' : ($c->is_image ? 'image' : null),
            ])
            ->all()
    );
@endphp

@if (! empty($coverage))
    <section class="block">
        <div class="block-head">
            <h2 class="block-title">{{ $heading }}</h2>
            <span class="block-more">{{ $note }}</span>
        </div>

        <div class="gc-grid">
            @foreach ($coverage as $item)
                @php
                    // Something we can show in the panel, or a plain outbound
                    // link when all we have is the original article.
                    $openable = $item['kind'] !== null && $item['file'];
                    $href     = $item['file'] ?: $item['external'];
                @endphp

                @if ($openable)
                    <button type="button" class="gc-card"
                            data-coverage
                            data-kind="{{ $item['kind'] }}"
                            data-src="{{ $item['file'] }}"
                            data-title="{{ $item['title'] }}"
                            @if ($item['external']) data-source="{{ $item['external'] }}" @endif>
                @elseif ($href)
                    <a class="gc-card" href="{{ $href }}" target="_blank" rel="noopener"
                       title="{{ $item['title'] }}">
                @else
                    <div class="gc-card">
                @endif

                    <div class="gc-cover">
                        @if ($item['cover'])
                            <img src="{{ $item['cover'] }}" alt="{{ $item['title'] }}" loading="lazy">
                        @else
                            {{-- No page image uploaded: the outlet's initials
                                 stand in, which reads better than a grey box. --}}
                            <span class="gc-initials">
                                {{ \Illuminate\Support\Str::of($item['outlet'] ?: $item['title'])->substr(0, 2)->upper() }}
                            </span>
                        @endif

                        @if ($item['badge'])
                            <span class="gc-pdf" aria-hidden="true">{{ $item['badge'] }}</span>
                        @endif
                    </div>

                    <span class="gc-caption">
                        @if ($item['outlet'])
                            <span class="gc-outlet">{{ $item['outlet'] }}</span>
                        @endif
                        {{ $item['title'] }}
                    </span>

                @if ($openable)
                    </button>
                @elseif ($href)
                    </a>
                @else
                    </div>
                @endif
            @endforeach
        </div>
    </section>

    {{-- One viewer for the whole grid, filled in on click. It starts empty so
         nothing is downloaded until a reader actually asks for it. --}}
    <div class="gc-viewer" id="coverage-viewer" hidden
         role="dialog" aria-modal="true" aria-labelledby="coverage-viewer-title">
        <div class="gc-viewer-backdrop" data-coverage-close></div>

        <div class="gc-viewer-panel" role="document">
            <header class="gc-viewer-bar">
                <h3 class="gc-viewer-title" id="coverage-viewer-title"></h3>

                <div class="gc-viewer-actions">
                    <a class="gc-viewer-btn" data-coverage-open target="_blank" rel="noopener">নতুন ট্যাবে</a>
                    <a class="gc-viewer-btn" data-coverage-source target="_blank" rel="noopener" hidden>মূল খবর</a>
                    <button class="gc-viewer-btn gc-viewer-x" type="button"
                            data-coverage-close aria-label="বন্ধ করুন">&times;</button>
                </div>
            </header>

            <div class="gc-viewer-body" data-coverage-body></div>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        var viewer = document.getElementById('coverage-viewer');
        if (!viewer) return;

        var body    = viewer.querySelector('[data-coverage-body]');
        var title   = viewer.querySelector('#coverage-viewer-title');
        var openBtn = viewer.querySelector('[data-coverage-open]');
        var srcBtn  = viewer.querySelector('[data-coverage-source]');
        var opener  = null;

        function close() {
            viewer.hidden = true;
            // Empty the panel so a PDF frame stops loading and nothing keeps
            // playing behind a closed dialog.
            body.innerHTML = '';
            document.body.style.overflow = '';

            if (opener) {
                opener.focus();
                opener = null;
            }
        }

        function open(card) {
            var kind = card.getAttribute('data-kind');
            var src  = card.getAttribute('data-src');
            var name = card.getAttribute('data-title') || '';
            var from = card.getAttribute('data-source');

            opener = card;
            title.textContent = name;
            openBtn.href = src;

            if (from) {
                srcBtn.href = from;
                srcBtn.hidden = false;
            } else {
                srcBtn.hidden = true;
            }

            if (kind === 'image') {
                var img = document.createElement('img');
                img.className = 'gc-viewer-image';
                img.src = src;
                img.alt = name;
                body.appendChild(img);
            } else {
                // A PDF in a frame: browsers render it with their own viewer,
                // so the reader gets page controls for free.
                var frame = document.createElement('iframe');
                frame.className = 'gc-viewer-frame';
                frame.src = src + '#view=FitH';
                frame.title = name;
                body.appendChild(frame);
            }

            viewer.hidden = false;
            // Stop the page behind scrolling while the panel is open.
            document.body.style.overflow = 'hidden';
            viewer.querySelector('.gc-viewer-x').focus();
        }

        document.addEventListener('click', function (e) {
            var card = e.target.closest('[data-coverage]');

            if (card) {
                e.preventDefault();
                open(card);
                return;
            }

            if (e.target.closest('[data-coverage-close]')) {
                close();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !viewer.hidden) close();
        });
    })();
    </script>
    @endpush
@endif
