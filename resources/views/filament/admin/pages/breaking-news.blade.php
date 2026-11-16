<x-filament-panels::page>

<style>
.bn { display: grid; gap: 1rem; --line: rgba(128,128,128,.28); }
.bn-note { padding: .7rem .9rem; border-radius: .5rem; font-weight: 600; font-size: .8125rem;
           background: #dcfce7; color: #166534; }
.bn-info { padding: .7rem .9rem; border-radius: .5rem; font-size: .8125rem;
           background: rgba(128,128,128,.12); }
.bn-warn { padding: .7rem .9rem; border-radius: .5rem; font-size: .8125rem; font-weight: 600;
           background: #fef3c7; color: #92400e; }

.bn-search { padding: .5rem .7rem; border: 1px solid var(--line); border-radius: .5rem;
             background: transparent; color: inherit; font: inherit; font-size: .8125rem; width: 100%; }

.bn-table { width: 100%; border-collapse: collapse; font-size: .8125rem; }
.bn-table th { text-align: start; font-size: .7rem; text-transform: uppercase; letter-spacing: .04em;
               opacity: .6; padding: .4rem .5rem; border-bottom: 1px solid var(--line); }
.bn-table td { padding: .5rem; border-bottom: 1px solid var(--line); vertical-align: middle; }
.bn-table tr:last-child td { border-bottom: 0; }
.bn-title { font-weight: 600; }
.bn-sub { font-size: .7rem; opacity: .6; }

.bn-btn { border: 0; border-radius: .5rem; padding: .4rem .9rem; cursor: pointer;
          font-size: .75rem; font-weight: 600; white-space: nowrap; }
.bn-add { background: #d32b2b; color: #fff; }
.bn-rm  { background: rgba(128,128,128,.22); color: inherit; }
.bn-btn:hover { opacity: .88; }

.bn-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
.bn-empty { padding: 1rem; opacity: .65; font-size: .8125rem; }
</style>

<div class="bn">

    @if ($notice !== '')
        <div class="bn-note">{{ $notice }}</div>
    @endif

    <div class="bn-info">
        The red strip at the top of the homepage shows <strong>{{ $limit }}</strong> stories.
        Choose them here, or choose nothing and it shows the {{ $limit }} newest articles by
        itself — a news homepage with an empty strip looks broken, so it is never left empty.
        You can also tick <strong>Breaking news</strong> on the article screen while writing it.
    </div>

    <x-filament::section>
        <x-slot name="heading">In the strip now</x-slot>

        @if ($chosen->isEmpty())
            <div class="bn-empty">
                Nothing chosen — the strip is showing the {{ $limit }} newest articles automatically.
            </div>
        @else
            @if ($chosen->count() > $limit)
                <div class="bn-warn">
                    {{ $chosen->count() }} stories are marked but the strip shows {{ $limit }}.
                    The newest {{ $limit }} are the ones readers see.
                </div>
            @endif

            <div class="bn-head" style="margin-bottom:.6rem">
                <span class="bn-sub">{{ $chosen->count() }} marked</span>
                <button type="button" class="bn-btn bn-rm" wire:click="clearAll"
                        wire:confirm="Remove every story from the breaking strip?">
                    Clear all
                </button>
            </div>

            <table class="bn-table">
                <tbody>
                @foreach ($chosen as $post)
                    <tr>
                        <td>
                            <div class="bn-title">{{ $post->title }}</div>
                            <div class="bn-sub">
                                {{ $post->category?->name ?? '—' }}
                                · {{ $post->published_at?->format('d M Y') ?? 'not published' }}
                                @if ($post->status !== 'published')
                                    · <strong>{{ $post->status }} — readers will not see this one</strong>
                                @endif
                            </div>
                        </td>
                        <td style="width:1%">
                            <button type="button" class="bn-btn bn-rm" wire:click="toggle({{ $post->id }})">
                                Remove
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">Add a story</x-slot>

        <input type="search" class="bn-search" placeholder="Search headlines…"
               wire:model.live.debounce.400ms="search">

        @if ($candidates->isEmpty())
            <div class="bn-empty">
                @if ($search !== '')
                    Nothing matches “{{ $search }}”.
                @else
                    No published articles to add.
                @endif
            </div>
        @else
            <table class="bn-table" style="margin-top:.7rem">
                <tbody>
                @foreach ($candidates as $post)
                    <tr>
                        <td>
                            <div class="bn-title">{{ $post->title }}</div>
                            <div class="bn-sub">
                                {{ $post->category?->name ?? '—' }}
                                · {{ $post->published_at?->format('d M Y') }}
                            </div>
                        </td>
                        <td style="width:1%">
                            <button type="button" class="bn-btn bn-add" wire:click="toggle({{ $post->id }})">
                                Add
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <p class="bn-sub" style="margin-top:.6rem">
                Showing the 40 newest. Use the search box for anything older.
            </p>
        @endif
    </x-filament::section>

</div>

</x-filament-panels::page>
