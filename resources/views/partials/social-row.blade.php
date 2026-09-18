@php $s = \App\Models\Setting::all_cached(); @endphp
<div class="social-row">
    <a class="s-fb" href="{{ ($s['facebook_url']  ?? '') ?: '#' }}" aria-label="Facebook"  rel="noopener">f</a>
    <a class="s-x"  href="{{ ($s['twitter_url']   ?? '') ?: '#' }}" aria-label="X"         rel="noopener">X</a>
    <a class="s-yt" href="{{ ($s['youtube_url']   ?? '') ?: '#' }}" aria-label="YouTube"   rel="noopener">&#9658;</a>
    <a class="s-ig" href="{{ ($s['instagram_url'] ?? '') ?: '#' }}" aria-label="Instagram" rel="noopener">&#9679;</a>
</div>
