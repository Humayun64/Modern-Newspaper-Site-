<x-filament-panels::page>

<style>
.gc { --line: rgba(128,128,128,.28); display: grid; gap: 1rem; }
.gc-note { padding: .7rem .9rem; border-radius: .5rem; font-weight: 600; font-size: .875rem;
           background: #dcfce7; color: #166534; }
.gc-bad { background: #fee2e2; color: #991b1b; }

.gc-form { display: grid; gap: .8rem; }
.gc-row { display: grid; gap: .6rem; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); }
.gc-field { display: grid; gap: .3rem; }
.gc-field span { font-size: .75rem; font-weight: 600; opacity: .65; }
.gc-field small { font-size: .7rem; opacity: .55; }
.gc-input, .gc-file, .gc-area {
    padding: .5rem .7rem; border: 1px solid var(--line); border-radius: .5rem;
    background: transparent; color: inherit; font: inherit; font-size: .8125rem; width: 100%;
}
.gc-file { padding: .4rem; }
.gc-btn { border: 0; border-radius: .5rem; padding: .55rem 1.1rem; cursor: pointer;
          font-size: .8125rem; font-weight: 600; background: #d32b2b; color: #fff; justify-self: start; }

.gc-table { width: 100%; border-collapse: collapse; font-size: .8125rem; }
.gc-table th { text-align: left; font-size: .72rem; font-weight: 600; opacity: .6;
               padding: .5rem .6rem; border-bottom: 1px solid var(--line); }
.gc-table td { padding: .6rem; border-bottom: 1px solid var(--line); vertical-align: middle; }
.gc-cover { width: 46px; height: 60px; object-fit: cover; border-radius: .25rem; background: rgba(128,128,128,.18); }
.gc-outlet { display: inline-block; padding: .15rem .5rem; border-radius: 999px; font-size: .68rem;
             font-weight: 700; background: rgba(128,128,128,.22); }
.gc-pdf { font-size: .72rem; font-weight: 700; color: #d32b2b; }
.gc-nopdf { font-size: .72rem; opacity: .45; }
.gc-actions { display: flex; gap: .4rem; justify-content: flex-end; }
.gc-x { background: none; border: 1px solid var(--line); border-radius: .35rem;
        color: inherit; opacity: .65; cursor: pointer; font-size: .72rem; padding: .25rem .55rem; }
.gc-x:hover { opacity: 1; }
.gc-x.is-danger:hover { background: #d32b2b; border-color: #d32b2b; color: #fff; }
.gc-off td { opacity: .45; }
.gc-empty { padding: 2rem; text-align: center; opacity: .6; font-size: .875rem; }
</style>

<div class="gc">

    @if (session('coverage_saved'))
        <div class="gc-note">{{ session('coverage_saved') }}</div>
    @endif

    @if ($errors->any())
        <div class="gc-note gc-bad">
            <strong>Check these fields:</strong>
            <ul style="margin:.4rem 0 0;padding-left:1.1rem;font-weight:500">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <x-filament::section>
        <x-slot name="heading">Add a highlight</x-slot>
        <x-slot name="description">
            Foreign coverage of Bangladesh. The PDF is what readers open — everything else is the card around it.
        </x-slot>

        <form method="POST" action="{{ route('admin.coverage.store') }}"
              enctype="multipart/form-data" class="gc-form">
            @csrf

            <div class="gc-row">
                <label class="gc-field" style="grid-column:1 / -1">
                    <span>Headline</span>
                    <input class="gc-input" type="text" name="title" required maxlength="255"
                           value="{{ old('title') }}">
                </label>
            </div>

            <div class="gc-row">
                <label class="gc-field">
                    <span>Outlet</span>
                    <input class="gc-input" type="text" name="outlet" maxlength="120"
                           placeholder="BBC, Al Jazeera…" value="{{ old('outlet') }}">
                </label>

                <label class="gc-field">
                    <span>Date published</span>
                    <input class="gc-input" type="date" name="published_on" value="{{ old('published_on') }}">
                </label>

                <label class="gc-field">
                    <span>Original link</span>
                    <input class="gc-input" type="url" name="source_url" maxlength="500"
                           placeholder="https://…" value="{{ old('source_url') }}">
                    <small>Used when there is no PDF.</small>
                </label>
            </div>

            <label class="gc-field">
                <span>Summary</span>
                <textarea class="gc-area" name="summary" rows="2" maxlength="400">{{ old('summary') }}</textarea>
            </label>

            <div class="gc-row">
                <label class="gc-field">
                    <span>PDF</span>
                    <input class="gc-file" type="file" name="pdf" accept="application/pdf">
                    <small>Up to 12 MB.</small>
                </label>

                <label class="gc-field">
                    <span>Cover image</span>
                    <input class="gc-file" type="file" name="cover" accept="image/*">
                    <small>Optional. A page thumbnail works well.</small>
                </label>
            </div>

            <button class="gc-btn" type="submit">Add highlight</button>
        </form>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">Highlights</x-slot>
        <x-slot name="description">Hidden entries stay here but do not appear on the site.</x-slot>

        @if ($items->isEmpty())
            <div class="gc-empty">Nothing added yet. The section stays off the homepage until there is.</div>
        @else
            <table class="gc-table">
                <thead>
                    <tr>
                        <th style="width:56px"></th>
                        <th>Headline</th>
                        <th style="width:120px">Outlet</th>
                        <th style="width:110px">Date</th>
                        <th style="width:70px">File</th>
                        <th style="width:150px"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr class="{{ $item->is_active ? '' : 'gc-off' }}">
                            <td>
                                @if ($item->cover_url)
                                    <img class="gc-cover" src="{{ $item->cover_url }}" alt="">
                                @else
                                    <div class="gc-cover"></div>
                                @endif
                            </td>

                            <td>
                                <strong>{{ $item->title }}</strong>
                                @if ($item->summary)
                                    <div style="opacity:.6;font-size:.75rem;margin-top:.2rem">
                                        {{ \Illuminate\Support\Str::limit($item->summary, 90) }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                @if ($item->outlet)
                                    <span class="gc-outlet">{{ $item->outlet }}</span>
                                @endif
                            </td>

                            <td>{{ $item->published_on?->format('d M Y') ?? '—' }}</td>

                            <td>
                                @if ($item->has_pdf)
                                    <a class="gc-pdf" href="{{ $item->link }}" target="_blank" rel="noopener">PDF</a>
                                @else
                                    <span class="gc-nopdf">link</span>
                                @endif
                            </td>

                            <td>
                                <div class="gc-actions">
                                    <form method="POST" action="{{ route('admin.coverage.toggle', $item) }}">
                                        @csrf
                                        <button class="gc-x" type="submit">
                                            {{ $item->is_active ? 'Hide' : 'Show' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.coverage.destroy', $item) }}"
                                          onsubmit="return confirm('Remove this highlight and its PDF?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="gc-x is-danger" type="submit">Remove</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-filament::section>

</div>

</x-filament-panels::page>
