@php
    use Illuminate\Support\Str;

    /*
     * The article's video, however it was given.
     *
     * An uploaded file plays here directly. A link is turned into a proper
     * embed where the host is one we know how to embed — a bare link under
     * the headline is a dead end on a phone, where nobody wants a new tab.
     * Anything we do not recognise stays a link, which is honest: better a
     * link that works than an iframe that shows an error.
     */
    $file = trim((string) ($post->video_file ?? ''));
    $url  = trim((string) ($post->video_url ?? ''));

    $fileUrl = $file === ''
        ? null
        : (Str::startsWith($file, ['http://', 'https://', '/']) ? $file : asset('storage/'.$file));

    $embed = null;

    if ($url !== '') {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if (Str::contains($host, 'youtube.com') && preg_match('/[?&]v=([\w-]{6,})/', $url, $m)) {
            $embed = 'https://www.youtube.com/embed/'.$m[1];
        } elseif (Str::contains($host, 'youtu.be') && preg_match('#youtu\.be/([\w-]{6,})#', $url, $m)) {
            $embed = 'https://www.youtube.com/embed/'.$m[1];
        } elseif (Str::contains($host, 'youtube.com') && preg_match('#/shorts/([\w-]{6,})#', $url, $m)) {
            $embed = 'https://www.youtube.com/embed/'.$m[1];
        } elseif (Str::contains($host, 'vimeo.com') && preg_match('#vimeo\.com/(\d+)#', $url, $m)) {
            $embed = 'https://player.vimeo.com/video/'.$m[1];
        } elseif (Str::contains($host, ['facebook.com', 'fb.watch'])) {
            $embed = 'https://www.facebook.com/plugins/video.php?href='.urlencode($url).'&show_text=false';
        }
    }
@endphp

@if ($fileUrl)
    <div class="post-video">
        {{-- preload="none" on purpose: a 50 MB file should not download
             itself for every reader who only came for the headline. --}}
        <video controls preload="none"
               @if ($post->featured_image) poster="{{ $post->thumb }}" @endif>
            <source src="{{ $fileUrl }}">
            আপনার ব্রাউজার এই ভিডিওটি চালাতে পারছে না।
            <a href="{{ $fileUrl }}">ভিডিওটি নামিয়ে নিন</a>।
        </video>
    </div>
@elseif ($embed)
    <div class="post-video post-video-embed">
        <iframe src="{{ $embed }}" title="{{ $post->title }}"
                loading="lazy" allowfullscreen
                allow="accelerometer; encrypted-media; gyroscope; picture-in-picture"></iframe>
    </div>
@elseif ($url !== '')
    <p><a class="chip" href="{{ $url }}" target="_blank" rel="noopener">ভিডিওটি দেখুন</a></p>
@endif
