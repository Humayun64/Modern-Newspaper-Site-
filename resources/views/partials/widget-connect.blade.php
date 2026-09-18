@php $s = \App\Models\Setting::all_cached(); @endphp

<section class="block">
    @include('partials.block-head', ['title' => 'আমাদের সাথে থাকুন'])
    <div class="connect-grid">
        <a class="c-fb" href="{{ ($s['facebook_url']  ?? '') ?: '#' }}" rel="noopener"><span class="ico">f</span>Facebook</a>
        <a class="c-x"  href="{{ ($s['twitter_url']   ?? '') ?: '#' }}" rel="noopener"><span class="ico">X</span>Twitter</a>
        <a class="c-in" href="{{ ($s['linkedin_url']  ?? '') ?: '#' }}" rel="noopener"><span class="ico">in</span>LinkedIn</a>
        <a class="c-vk" href="{{ ($s['vk_url']        ?? '') ?: '#' }}" rel="noopener"><span class="ico">&#9679;</span>VK</a>
        <a class="c-yt" href="{{ ($s['youtube_url']   ?? '') ?: '#' }}" rel="noopener"><span class="ico">&#9658;</span>YouTube</a>
        <a class="c-ig" href="{{ ($s['instagram_url'] ?? '') ?: '#' }}" rel="noopener"><span class="ico">&#9673;</span>Instagram</a>
    </div>
</section>
