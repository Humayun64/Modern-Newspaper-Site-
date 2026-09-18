@php
    $s = \App\Models\Setting::all_cached();
    $editor = \App\Models\User::where('role', 'admin')->first();
@endphp

@if ($editor)
    <section class="block author-card">
        @include('partials.block-head', ['title' => 'সম্পাদকীয়'])
        <img src="{{ $editor->photo ? asset('storage/'.$editor->photo) : 'https://ui-avatars.com/api/?name='.urlencode($editor->name).'&size=188&background=d32b2b&color=fff' }}"
             alt="{{ $editor->display_name }}" loading="lazy">
        <p class="author-name">{{ $editor->display_name }}</p>
        <p>{{ $editor->bio ?: ($s['about_text'] ?? 'সত্য ও নিরপেক্ষ সংবাদ পরিবেশনই আমাদের অঙ্গীকার।') }}</p>
        @include('partials.social-row')
    </section>
@endif
