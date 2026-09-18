@extends('layouts.app')

@section('title', 'পাতাটি পাওয়া যায়নি')

@section('content')
<div class="body-row">
    <section class="block" style="text-align:center;padding:48px 24px">
        <p style="font-family:var(--serif);font-size:64px;font-weight:700;color:var(--accent);margin:0;line-height:1">৪০৪</p>
        <h1 style="font-family:var(--serif);font-size:26px;margin:12px 0 8px">পাতাটি পাওয়া যায়নি</h1>
        <p style="color:var(--ink-soft);max-width:46ch;margin:0 auto 22px">
            খবরটি সরিয়ে ফেলা হয়েছে, অথবা ঠিকানায় ভুল আছে। নিচে খুঁজে দেখুন বা হোমপেজে ফিরে যান।
        </p>

        <form action="{{ route('search') }}" method="get" role="search"
              style="display:flex;max-width:420px;margin:0 auto 22px">
            <input type="search" name="q" placeholder="যা খুঁজছেন লিখুন…" aria-label="খুঁজুন"
                   style="flex:1;padding:11px 14px;border:1px solid var(--rule);border-right:0;font-family:var(--sans);font-size:15px">
            <button type="submit"
                    style="background:var(--accent);color:#fff;border:0;padding:0 22px;font-family:var(--sans);font-weight:600;cursor:pointer">
                খুঁজুন
            </button>
        </form>

        <a class="chip" href="{{ route('home') }}">হোমপেজে ফিরুন</a>
    </section>

    <aside class="sidebar">
        @include('partials.widget-categories')
    </aside>
</div>
@endsection
