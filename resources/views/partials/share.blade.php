@php
    $shareUrl   = route('post.show', $post->slug);
    $shareTitle = $post->title;
    $enc        = rawurlencode($shareUrl);
    $encTitle   = rawurlencode($shareTitle);
@endphp

<div class="share" data-share-url="{{ $shareUrl }}">
    <span class="share-label">শেয়ার করুন</span>

    <a class="share-btn s-fb" target="_blank" rel="noopener"
       href="https://www.facebook.com/sharer/sharer.php?u={{ $enc }}" aria-label="Facebook">
        <span aria-hidden="true">f</span><span class="share-text">Facebook</span>
    </a>

    {{-- WhatsApp first after Facebook: in Bangladesh it moves more traffic than X. --}}
    <a class="share-btn s-wa" target="_blank" rel="noopener"
       href="https://api.whatsapp.com/send?text={{ $encTitle }}%20{{ $enc }}" aria-label="WhatsApp">
        <span aria-hidden="true">&#9990;</span><span class="share-text">WhatsApp</span>
    </a>

    <a class="share-btn s-x" target="_blank" rel="noopener"
       href="https://twitter.com/intent/tweet?url={{ $enc }}&text={{ $encTitle }}" aria-label="X">
        <span aria-hidden="true">X</span><span class="share-text">X</span>
    </a>

    <a class="share-btn s-tg" target="_blank" rel="noopener"
       href="https://t.me/share/url?url={{ $enc }}&text={{ $encTitle }}" aria-label="Telegram">
        <span aria-hidden="true">&#9993;</span><span class="share-text">Telegram</span>
    </a>

    <button type="button" class="share-btn s-copy" data-copy aria-label="Copy link">
        <span aria-hidden="true">&#128279;</span><span class="share-text">লিংক কপি</span>
    </button>
</div>
