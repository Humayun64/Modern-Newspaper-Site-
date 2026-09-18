<x-filament-panels::page>

<style>
.rd { --line: rgba(128,128,128,.28); display: grid; gap: 1rem; }
.rd-note { padding: .7rem .9rem; border-radius: .5rem; font-weight: 600; font-size: .875rem; }
.rd-ok { background: #dcfce7; color: #166534; }
.rd-bad { background: #fee2e2; color: #991b1b; }

.rd-tiles { display: grid; gap: .8rem; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); }
.rd-tile { border: 1px solid var(--line); border-radius: .6rem; padding: .8rem 1rem; }
.rd-tile-label { font-size: .72rem; opacity: .6; margin: 0 0 .25rem; }
.rd-tile-value { font-size: 1.5rem; font-weight: 700; margin: 0; }

.rd-row { display: flex; gap: .6rem; flex-wrap: wrap; align-items: flex-end; }
.rd-field { display: grid; gap: .3rem; flex: 1; min-width: 190px; }
.rd-field span { font-size: .75rem; font-weight: 600; opacity: .65; }
.rd-input, .rd-select {
    padding: .5rem .7rem; border: 1px solid var(--line); border-radius: .5rem;
    background: transparent; color: inherit; font: inherit; font-size: .8125rem; width: 100%;
}
.rd-btn { border: 0; border-radius: .5rem; padding: .55rem 1rem; cursor: pointer;
          font-size: .8125rem; font-weight: 600; background: #d32b2b; color: #fff; }

.rd-table { width: 100%; border-collapse: collapse; font-size: .8125rem; }
.rd-table th { text-align: left; font-size: .72rem; font-weight: 600; opacity: .6;
               padding: .5rem .6rem; border-bottom: 1px solid var(--line); }
.rd-table td { padding: .6rem .6rem; border-bottom: 1px solid var(--line); vertical-align: middle; }
.rd-path { font-family: ui-monospace, Menlo, Consolas, monospace; font-size: .75rem; word-break: break-all; }
.rd-arrow { opacity: .45; }
.rd-pill { display: inline-block; padding: .15rem .5rem; border-radius: 999px; font-size: .68rem; font-weight: 700;
           background: rgba(128,128,128,.22); }
.rd-hits { font-variant-numeric: tabular-nums; text-align: right; }
.rd-x { background: none; border: 1px solid var(--line); border-radius: .35rem;
        color: inherit; opacity: .6; cursor: pointer; font-size: .75rem; padding: .25rem .5rem; }
.rd-x:hover { opacity: 1; background: #d32b2b; border-color: #d32b2b; color: #fff; }
.rd-empty { padding: 2rem; text-align: center; opacity: .6; font-size: .875rem; }
</style>

<div class="rd">

    @if (session('redirect_saved'))
        <div class="rd-note rd-ok">Saved.</div>
    @endif

    @if ($errors->any())
        <div class="rd-note rd-bad">
            <ul style="margin:0;padding-left:18px">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="rd-tiles">
        <div class="rd-tile">
            <p class="rd-tile-label">Total redirects</p>
            <p class="rd-tile-value">{{ number_format($total) }}</p>
        </div>
        <div class="rd-tile">
            <p class="rd-tile-label">From the import</p>
            <p class="rd-tile-value">{{ number_format($imported) }}</p>
        </div>
        <div class="rd-tile">
            <p class="rd-tile-label">Actually used</p>
            <p class="rd-tile-value">{{ number_format($used) }}</p>
        </div>
    </div>

    <x-filament::section>
        <x-slot name="heading">Add a redirect</x-slot>
        <x-slot name="description">Paste a full old URL or just its path — both work.</x-slot>

        <form method="POST" action="{{ route('admin.redirects.store') }}" class="rd-row">
            @csrf
            <label class="rd-field">
                <span>Old address</span>
                <input class="rd-input" name="from_path" required placeholder="2026/09/15/some-old-slug">
            </label>
            <label class="rd-field">
                <span>Send to</span>
                <input class="rd-input" name="to_path" required placeholder="some-new-slug">
            </label>
            <label class="rd-field" style="flex:0 0 150px">
                <span>Type</span>
                <select class="rd-select" name="status">
                    <option value="301">301 — permanent</option>
                    <option value="302">302 — temporary</option>
                </select>
            </label>
            <button class="rd-btn" type="submit">Add</button>
        </form>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">All redirects</x-slot>

        <form method="GET" class="rd-row" style="margin-bottom:1rem">
            <label class="rd-field">
                <span>Search</span>
                <input class="rd-input" name="q" value="{{ $search }}" placeholder="Part of an address">
            </label>
            <button class="rd-btn" type="submit">Search</button>
        </form>

        @if ($redirects->isEmpty())
            <p class="rd-empty">
                Nothing here yet. Running <code>php artisan import:wordpress</code> fills this
                automatically, one row per imported article.
            </p>
        @else
            <div style="overflow-x:auto">
                <table class="rd-table">
                    <thead>
                        <tr><th>Old address</th><th></th><th>Goes to</th><th>Type</th><th>Source</th><th class="rd-hits">Used</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($redirects as $r)
                            <tr>
                                <td class="rd-path">/{{ $r->from_path }}</td>
                                <td class="rd-arrow" aria-hidden="true">&rarr;</td>
                                <td class="rd-path">/{{ $r->to_path }}</td>
                                <td><span class="rd-pill">{{ $r->status }}</span></td>
                                <td><span class="rd-pill">{{ $r->source }}</span></td>
                                <td class="rd-hits">
                                    {{ number_format($r->hits) }}
                                    @if ($r->last_used_at)
                                        <br><span style="opacity:.55;font-size:.7rem">{{ $r->last_used_at->diffForHumans() }}</span>
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.redirects.destroy', $r) }}">
                                        @csrf @method('DELETE')
                                        <button class="rd-x" type="submit">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="display:flex;justify-content:center;padding-top:1rem">{{ $redirects->links() }}</div>
        @endif
    </x-filament::section>

    <p style="font-size:.78rem;opacity:.6;margin:0;line-height:1.7">
        A visitor or search engine hitting an old address gets a 301 to the new one, which is how
        Google transfers the ranking rather than dropping it. The "Used" column is the proof it is
        working — after the domain switches over, those numbers should start climbing.
    </p>

</div>

</x-filament-panels::page>
