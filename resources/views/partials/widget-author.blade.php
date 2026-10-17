@php
    $s = \App\Models\Setting::all_cached();

    /*
     * Correction 22: the editorial card carries the client's own name.
     *
     * Site settings → Editorial wins when it has been filled in. With it
     * empty the card falls back to the admin account, which is how this
     * worked before — so the card never disappears just because nobody has
     * typed a name in yet.
     */
    $editor = \App\Models\User::where('role', 'admin')->first();

    $name = trim((string) ($s['editor_name'] ?? '')) ?: ($editor?->display_name);
    $role = trim((string) ($s['editor_role'] ?? ''));
    $bio  = trim((string) ($s['editor_bio'] ?? ''))
            ?: ($editor?->bio ?: ($s['about_text'] ?? 'সত্য ও নিরপেক্ষ সংবাদ পরিবেশনই আমাদের অঙ্গীকার।'));

    $photo = trim((string) ($s['editor_photo'] ?? ''));

    $photoUrl = match (true) {
        $photo !== ''     => \Illuminate\Support\Str::startsWith($photo, ['http://', 'https://', '/'])
                                ? $photo
                                : asset('storage/'.$photo),
        (bool) $editor?->photo => asset('storage/'.$editor->photo),
        default           => 'https://ui-avatars.com/api/?name='.urlencode((string) $name)
                                .'&size=188&background=d32b2b&color=fff',
    };
@endphp

@if (filled($name))
    <section class="block author-card">
        @include('partials.block-head', ['title' => 'সম্পাদকীয়', 'category' => null])

        <img src="{{ $photoUrl }}" alt="{{ $name }}" loading="lazy">

        <p class="author-name">{{ $name }}</p>

        @if ($role !== '')
            <p class="author-role">{{ $role }}</p>
        @endif

        <p>{{ $bio }}</p>

        @include('partials.social-row')
    </section>
@endif
