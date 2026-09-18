@php $uid = 'tabs-'.uniqid(); $popularList = $popular ?? collect(); @endphp

<section class="block">
    <div class="tabs" role="tablist">
        <button class="tab-btn" role="tab" aria-selected="true"  data-panel="{{ $uid }}-latest">&#128337; সর্বশেষ</button>
        <button class="tab-btn" role="tab" aria-selected="false" data-panel="{{ $uid }}-popular">&#9889; জনপ্রিয়</button>
    </div>

    <div class="tab-panel" id="{{ $uid }}-latest">
        @foreach ($posts as $post)
            @include('partials.row-card', ['post' => $post])
        @endforeach
    </div>

    <div class="tab-panel" id="{{ $uid }}-popular" hidden>
        @forelse ($popularList as $post)
            @include('partials.row-card', ['post' => $post])
        @empty
            <p style="color:var(--ink-soft);font-size:14px">এখনও পর্যাপ্ত তথ্য নেই।</p>
        @endforelse
    </div>
</section>
