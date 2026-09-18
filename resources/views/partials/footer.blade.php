@php
    $settings   = \App\Models\Setting::all_cached();
    $footerCats = \App\Models\Category::active()->limit(8)->get();
    $footerTags = \App\Models\Tag::orderByDesc('posts_count')->limit(8)->get();

    // Built in the menu builder; falls back to published footer pages.
    $footerMenu = \App\Models\Menu::tree('footer');

    if (empty($footerMenu)) {
        $footerMenu = \App\Models\Page::published()->where('show_in_footer', true)
            ->orderBy('sort_order')->get()
            ->map(fn ($p) => ['label' => $p->title, 'url' => route('page.show', $p->slug), 'target' => null])
            ->all();
    }

    // Plain arrays in the cache — a serialised model can come back unusable.
    $recent = \Illuminate\Support\Facades\Cache::remember('footer.recent', 300, fn () =>
        \App\Models\Post::published()->latestFirst()->limit(5)->get(['id', 'title', 'slug'])
            ->map(fn ($p) => ['title' => $p->title, 'url' => route('post.show', $p->slug)])->all()
    );
@endphp

<footer class="footer">
    <div class="wrap">
        <div class="footer-cols">

            <div>
                <h3>আমাদের সম্পর্কে</h3>
                <p>{{ $settings['about_text'] ?? 'আমারদেশ২৪.নিউজ একটি বিশ্বস্ত ও নির্ভরযোগ্য অনলাইন সংবাদমাধ্যম।' }}</p>
                @if ($settings['site_email'] ?? null)
                    <p>ইমেইল: {{ $settings['site_email'] }}</p>
                @endif
                @if ($settings['site_address'] ?? null)
                    <p>{{ $settings['site_address'] }}</p>
                @endif
            </div>

            <div>
                <h3>ট্যাগ</h3>
                <div class="chip-row">
                    @foreach ($footerTags as $tag)
                        <a class="chip" href="{{ route('tag.show', $tag) }}">{{ $tag->name }}</a>
                    @endforeach
                </div>

                <div class="footer-sub">
                    <h3>বিভাগ</h3>
                    <div class="chip-row">
                        @foreach ($footerCats as $cat)
                            <a class="chip" href="{{ route('category.show', $cat) }}">{{ $cat->name }}</a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div>
                <h3>সাম্প্রতিক খবর</h3>
                <ul class="footer-list">
                    @foreach ($recent as $item)
                        <li><a href="{{ $item['url'] }}">{{ $item['title'] }}</a></li>
                    @endforeach
                </ul>
            </div>

        </div>

        @if (!empty($footerMenu))
            <div class="footer-nav">
                @foreach ($footerMenu as $item)
                    <a href="{{ $item['url'] }}"
                       @if ($item['target'] ?? null) target="{{ $item['target'] }}" rel="noopener" @endif>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
        @endif

        <div class="footer-bottom">
            © {{ \App\Support\Bangla::digits(date('Y')) }} {{ $settings['site_name'] ?? 'amarDesh24.news' }} — সর্বস্বত্ব সংরক্ষিত।
        </div>
    </div>
</footer>
