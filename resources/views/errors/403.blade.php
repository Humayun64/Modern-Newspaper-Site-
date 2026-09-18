@extends('layouts.app')

@section('title', 'প্রবেশাধিকার নেই')

@section('content')
<div class="body-row">
    <section class="block" style="text-align:center;padding:48px 24px">
        <p style="font-family:var(--serif);font-size:64px;font-weight:700;color:var(--accent);margin:0;line-height:1">৪০৩</p>
        <h1 style="font-family:var(--serif);font-size:26px;margin:12px 0 8px">এই পাতায় প্রবেশাধিকার নেই</h1>
        <p style="color:var(--ink-soft);max-width:46ch;margin:0 auto 22px">
            আপনার অ্যাকাউন্টের জন্য এই অংশটি উন্মুক্ত নয়। প্রয়োজন হলে সম্পাদকের সঙ্গে যোগাযোগ করুন।
        </p>
        <a class="chip" href="{{ route('home') }}">হোমপেজে ফিরুন</a>
    </section>

    <aside class="sidebar"></aside>
</div>
@endsection
