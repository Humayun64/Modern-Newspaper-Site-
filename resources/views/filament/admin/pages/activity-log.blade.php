<x-filament-panels::page>

<style>
.al { --line: rgba(128,128,128,.28); display: grid; gap: 1rem; }
.al-warn {
    display: flex; gap: .7rem; align-items: center;
    padding: .75rem .95rem; border-radius: .5rem;
    background: rgba(208,59,59,.12); border: 1px solid rgba(208,59,59,.4); font-size: .875rem;
}
.al-warn .ico {
    width: 24px; height: 24px; flex-shrink: 0; border-radius: 50%;
    background: #d03b3b; color: #fff; display: grid; place-items: center; font-weight: 700; font-size: .8rem;
}

.al-filters { display: flex; gap: .6rem; flex-wrap: wrap; align-items: flex-end; }
.al-field { display: grid; gap: .3rem; }
.al-field span { font-size: .75rem; font-weight: 600; opacity: .65; }
.al-select {
    padding: .5rem .7rem; border: 1px solid var(--line); border-radius: .5rem;
    background: transparent; color: inherit; font: inherit; font-size: .8125rem; min-width: 180px;
}
.al-btn { border: 0; border-radius: .5rem; padding: .55rem 1rem; cursor: pointer;
          font-size: .8125rem; font-weight: 600; background: #d32b2b; color: #fff; }
.al-clear { font-size: .8125rem; text-decoration: none; color: inherit; opacity: .7; padding: .55rem 0; }

.al-table { width: 100%; border-collapse: collapse; font-size: .8125rem; }
.al-table th { text-align: left; font-size: .72rem; font-weight: 600; opacity: .6;
               padding: .5rem .6rem; border-bottom: 1px solid var(--line); }
.al-table td { padding: .65rem .6rem; border-bottom: 1px solid var(--line); vertical-align: top; }
.al-when { white-space: nowrap; font-variant-numeric: tabular-nums; opacity: .75; }
.al-who { font-weight: 600; }
.al-what { display: inline-flex; align-items: center; gap: .45rem; font-weight: 600; }
.al-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.tone-critical { background: #d03b3b; } .tone-serious { background: #ec835a; }
.tone-good { background: #0ca30c; }     .tone-neutral { background: rgba(128,128,128,.55); }
.al-desc { opacity: .75; }
.al-ip { font-variant-numeric: tabular-nums; opacity: .6; white-space: nowrap; }
.al-empty { padding: 2rem; text-align: center; opacity: .6; font-size: .875rem; }
.al-pager { display: flex; justify-content: center; padding-top: .5rem; }
</style>

<div class="al">

    @if ($failedToday > 0)
        <div class="al-warn">
            <span class="ico" aria-hidden="true">!</span>
            <span>
                <strong>{{ $failedToday }} failed sign-in {{ \Illuminate\Support\Str::plural('attempt', $failedToday) }} today.</strong>
                A handful is usually a forgotten password. Dozens from one address is someone guessing —
                check the IP column below.
            </span>
        </div>
    @endif

    <form method="GET" class="al-filters">
        <label class="al-field">
            <span>Action</span>
            <select class="al-select" name="action">
                <option value="">All actions</option>
                @foreach ($actions as $value => $label)
                    <option value="{{ $value }}" @selected($action === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label class="al-field">
            <span>Person</span>
            <select class="al-select" name="user">
                <option value="">Everyone</option>
                @foreach ($users as $u)
                    <option value="{{ $u->id }}" @selected((string) $userId === (string) $u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
        </label>

        <button class="al-btn" type="submit">Filter</button>

        @if ($action || $userId)
            <a class="al-clear" href="{{ \App\Filament\Admin\Pages\ActivityLogPage::getUrl() }}">Clear</a>
        @endif
    </form>

    <x-filament::section>
        @if ($logs->isEmpty())
            <p class="al-empty">Nothing recorded yet for this filter.</p>
        @else
            <div style="overflow-x:auto">
                <table class="al-table">
                    <thead>
                        <tr><th>When</th><th>Who</th><th>What</th><th>Detail</th><th>IP</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td class="al-when">
                                    {{ $log->created_at?->format('d M, h:i A') }}
                                    <br><span style="opacity:.6">{{ $log->created_at?->diffForHumans() }}</span>
                                </td>
                                <td class="al-who">{{ $log->user?->name ?? $log->user_name ?? '—' }}</td>
                                <td>
                                    <span class="al-what">
                                        <span class="al-dot tone-{{ $log->tone }}" aria-hidden="true"></span>
                                        {{ $log->action_label }}
                                    </span>
                                </td>
                                <td class="al-desc">{{ $log->description }}</td>
                                <td class="al-ip">{{ $log->ip }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="al-pager">{{ $logs->links() }}</div>
        @endif
    </x-filament::section>

    <p style="font-size:.78rem;opacity:.6;margin:0">
        Sign-ins, article changes, settings and menu edits, and user changes are recorded.
        Entries are kept indefinitely — if the table grows large we can add automatic pruning.
    </p>

</div>

</x-filament-panels::page>
