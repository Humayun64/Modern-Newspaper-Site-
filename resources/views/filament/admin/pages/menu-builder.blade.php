<x-filament-panels::page>

<style>
.mb { --line: rgba(128,128,128,.28); --soft: rgba(128,128,128,.10); display: grid; gap: 1rem; }
.mb-tabs { display: flex; gap: .5rem; }
.mb-tab {
    padding: .45rem .95rem; border-radius: .5rem; font-size: .875rem; font-weight: 600;
    border: 1px solid var(--line); text-decoration: none; color: inherit;
}
.mb-tab[aria-current="true"] { background: #d32b2b; border-color: #d32b2b; color: #fff; }

.mb-cols { display: grid; gap: 1rem; grid-template-columns: 300px minmax(0, 1fr); align-items: start; }
@media (max-width: 900px) { .mb-cols { grid-template-columns: 1fr; } }

.mb-panel { border: 1px solid var(--line); border-radius: .75rem; overflow: hidden; }
.mb-panel + .mb-panel { margin-top: .75rem; }
.mb-panel > summary {
    padding: .7rem .9rem; font-size: .875rem; font-weight: 600; cursor: pointer;
    background: var(--soft); list-style: none;
}
.mb-panel > summary::-webkit-details-marker { display: none; }
.mb-panel > summary::after { content: '+'; float: right; opacity: .6; }
.mb-panel[open] > summary::after { content: '\2013'; }
.mb-panel-body { padding: .8rem .9rem; display: grid; gap: .6rem; max-height: 300px; overflow: auto; }

.mb-check { display: flex; gap: .55rem; align-items: center; font-size: .8125rem; }
.mb-muted { opacity: .55; font-size: .72rem; }

.mb-btn { border: 0; border-radius: .5rem; padding: .5rem .9rem; cursor: pointer;
          font-size: .8125rem; font-weight: 600; background: #d32b2b; color: #fff; }
.mb-btn-ghost { background: transparent; border: 1px solid var(--line); color: inherit; }
.mb-input { width: 100%; padding: .48rem .6rem; border: 1px solid var(--line);
            border-radius: .45rem; background: transparent; color: inherit; font: inherit; font-size: .8125rem; }

/* ---------- the tree ---------- */
.mb-tree, .mb-sub { list-style: none; margin: 0; padding: 0; display: grid; gap: .5rem; }
.mb-tree { min-height: 60px; }

/* A sub-list is a real drop target: wide, tall enough to hit, and it says so
   when empty. The previous version was a 14px sliver nobody could land on. */
.mb-sub {
    margin: .5rem 0 .1rem 2.4rem;
    padding: .1rem 0 .1rem .9rem;
    border-left: 2px dashed var(--line);
    min-height: 16px;
}
.mb-sub-empty {
    min-height: 44px;
    border: 2px dashed var(--line); border-radius: .5rem;
    padding: .55rem .8rem; margin-left: 2.4rem;
    display: grid; align-content: center;
}
.mb-sub-empty::after {
    content: 'Drop here to make this a sub-item';
    font-size: .72rem; opacity: .5; font-style: italic;
}
.mb-item[data-depth="2"] > .mb-sub { display: none; }

.mb-item { border: 1px solid var(--line); border-radius: .6rem; background: var(--soft); }
.mb-item[data-depth="2"] { background: transparent; }

.mb-head {
    display: grid;
    grid-template-columns: 24px minmax(0,1fr) auto auto auto auto;
    gap: .5rem; align-items: center; padding: .55rem .7rem;
}
.mb-grip { cursor: grab; opacity: .5; font-size: 1rem; line-height: 1; text-align: center; }
.mb-grip:active { cursor: grabbing; }
.mb-name { min-width: 0; }
.mb-label { font-size: .875rem; font-weight: 600; display: block; }
.mb-kind { font-size: .7rem; opacity: .55; text-transform: capitalize; }

.mb-step {
    background: transparent; border: 1px solid var(--line); border-radius: .35rem;
    color: inherit; cursor: pointer; font-size: .8rem; line-height: 1; padding: .3rem .5rem;
}
.mb-step:hover:not(:disabled) { background: #d32b2b; border-color: #d32b2b; color: #fff; }
.mb-step:disabled { opacity: .25; cursor: not-allowed; }

.mb-x { background: none; border: 0; color: inherit; opacity: .5; cursor: pointer; font-size: 1rem; padding: 0 .3rem; }
.mb-x:hover { opacity: 1; color: #d32b2b; }
.mb-toggle { background: none; border: 0; color: inherit; opacity: .6; cursor: pointer; font-size: .75rem; }
.mb-edit { display: none; padding: 0 .7rem .7rem; gap: .5rem; }
.mb-item.is-open > .mb-edit { display: grid; }
.mb-sortable-ghost { opacity: .3; }

.mb-hint { font-size: .78rem; opacity: .62; margin: 0; line-height: 1.6; }
.mb-note { padding: .7rem .9rem; border-radius: .5rem; font-weight: 600; font-size: .875rem; }
.mb-ok { background: #dcfce7; color: #166534; }
.mb-empty { border: 2px dashed var(--line); border-radius: .6rem; padding: 1.4rem; text-align: center; font-size: .8125rem; opacity: .6; }
</style>

<div class="mb">

    @if (session('menu_saved'))
        <div class="mb-note mb-ok">Menu saved.</div>
    @endif

    <div class="mb-tabs">
        <a class="mb-tab" href="{{ \App\Filament\Admin\Pages\MenuBuilder::getUrl() }}?menu=header"
           aria-current="{{ $location === 'header' ? 'true' : 'false' }}">Header menu</a>
        <a class="mb-tab" href="{{ \App\Filament\Admin\Pages\MenuBuilder::getUrl() }}?menu=footer"
           aria-current="{{ $location === 'footer' ? 'true' : 'false' }}">Footer menu</a>
    </div>

    <p class="mb-hint">
        Drag the ⠿ handle to reorder. To make a dropdown, press <strong>→</strong> on an item and it
        becomes a sub-item of the one above it; <strong>←</strong> moves it back out. You can also drag
        an item into the dashed box under another. The navigation renders two levels.
    </p>

    <form method="POST" action="{{ route('admin.menu.save') }}" id="mb-form">
        @csrf
        <input type="hidden" name="location" value="{{ $location }}">
        <input type="hidden" name="items" id="mb-items">

        <div class="mb-cols">

            <div>
                <details class="mb-panel" open>
                    <summary>Categories</summary>
                    <div class="mb-panel-body">
                        @forelse ($categories as $cat)
                            <label class="mb-check">
                                <input type="checkbox" data-add="category" data-id="{{ $cat->id }}" data-label="{{ $cat->name }}">
                                <span>{{ $cat->name }}</span>
                            </label>
                        @empty
                            <p class="mb-muted">No categories yet.</p>
                        @endforelse
                        <button type="button" class="mb-btn mb-btn-ghost" data-add-group="category">Add to menu</button>
                    </div>
                </details>

                <details class="mb-panel">
                    <summary>Pages</summary>
                    <div class="mb-panel-body">
                        @forelse ($pages as $pg)
                            <label class="mb-check">
                                <input type="checkbox" data-add="page" data-id="{{ $pg->id }}" data-label="{{ $pg->title }}">
                                <span>{{ $pg->title }}
                                    @if ($pg->status !== 'published')
                                        <span class="mb-muted">(draft — hidden until published)</span>
                                    @endif
                                </span>
                            </label>
                        @empty
                            <p class="mb-muted">No pages yet.</p>
                        @endforelse
                        <button type="button" class="mb-btn mb-btn-ghost" data-add-group="page">Add to menu</button>
                    </div>
                </details>

                <details class="mb-panel">
                    <summary>Custom link</summary>
                    <div class="mb-panel-body">
                        <input class="mb-input" id="mb-custom-label" placeholder="Link text">
                        <input class="mb-input" id="mb-custom-url" placeholder="https://…">
                        <button type="button" class="mb-btn mb-btn-ghost" id="mb-add-custom">Add to menu</button>
                    </div>
                </details>

                <details class="mb-panel">
                    <summary>Home link</summary>
                    <div class="mb-panel-body">
                        <input class="mb-input" id="mb-home-label" value="হোম" placeholder="Link text">
                        <button type="button" class="mb-btn mb-btn-ghost" id="mb-add-home">Add to menu</button>
                    </div>
                </details>
            </div>

            <div>
                <ul class="mb-tree" id="mb-root">
                    @include('filament.admin.pages.partials.menu-nodes', ['nodes' => $tree])
                </ul>

                <div class="mb-empty" id="mb-empty" @if(count($tree)) style="display:none" @endif>
                    This menu is empty. Add categories or pages from the left.
                </div>

                <div style="margin-top:1rem">
                    <button type="submit" class="mb-btn">Save menu</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
<script>
(function () {
    var root  = document.getElementById('mb-root');
    var form  = document.getElementById('mb-form');
    var field = document.getElementById('mb-items');
    var empty = document.getElementById('mb-empty');

    var MAX_DEPTH = 2;

    function sub(li)      { return li.querySelector(':scope > .mb-sub'); }
    function hasKids(li)  { var s = sub(li); return !!s && s.children.length > 0; }

    function depthOf(li) {
        var d = 1, node = li.parentElement;
        while (node && node !== root) {
            if (node.classList.contains('mb-sub')) d++;
            node = node.parentElement;
        }
        return d;
    }

    /* Recomputed after every change: depth attributes, empty drop zones,
       and which arrows are usable. Keeping this in one place means the
       buttons and the drag behaviour can never disagree. */
    function refresh() {
        root.querySelectorAll('.mb-item').forEach(function (li) {
            var d = depthOf(li);
            li.dataset.depth = d;

            var s = sub(li);
            if (s) {
                s.classList.toggle('mb-sub-empty', s.children.length === 0 && d < MAX_DEPTH);
            }

            var inBtn  = li.querySelector(':scope > .mb-head > .mb-in');
            var outBtn = li.querySelector(':scope > .mb-head > .mb-out');

            if (inBtn) {
                // Needs somewhere to go, room to go there, and no children of
                // its own that would end up a level too deep.
                inBtn.disabled = !li.previousElementSibling || d >= MAX_DEPTH || hasKids(li);
            }
            if (outBtn) {
                outBtn.disabled = d === 1;
            }
        });

        empty.style.display = root.children.length ? 'none' : '';
    }

    function makeSortable(list) {
        new Sortable(list, {
            group: 'menu',
            handle: '.mb-grip',
            animation: 140,
            fallbackOnBody: true,
            swapThreshold: 0.65,
            emptyInsertThreshold: 25,   // makes empty sub-lists easy to drop into
            ghostClass: 'mb-sortable-ghost',
            onMove: function (evt) {
                // Never let a drag create a third level.
                if (evt.to !== root && hasKids(evt.dragged)) return false;
                return true;
            },
            onSort: refresh,
            onEnd: refresh
        });
    }

    function buildItem(data) {
        var li = document.createElement('li');
        li.className = 'mb-item';
        li.dataset.type = data.type;
        li.dataset.referenceId = data.reference_id || '';

        li.innerHTML =
            '<div class="mb-head">' +
                '<span class="mb-grip" aria-hidden="true">⠿</span>' +
                '<span class="mb-name"><span class="mb-label"></span><span class="mb-kind"></span></span>' +
                '<button type="button" class="mb-step mb-out" title="Move out one level">←</button>' +
                '<button type="button" class="mb-step mb-in" title="Make it a sub-item of the one above">→</button>' +
                '<button type="button" class="mb-toggle">Edit</button>' +
                '<button type="button" class="mb-x" aria-label="Remove">×</button>' +
            '</div>' +
            '<div class="mb-edit">' +
                '<input class="mb-input mb-edit-label" placeholder="Link text">' +
                (data.type === 'custom' ? '<input class="mb-input mb-edit-url" placeholder="https://…">' : '') +
                '<label class="mb-check"><input type="checkbox" class="mb-edit-tab"> Open in a new tab</label>' +
            '</div>' +
            '<ul class="mb-sub"></ul>';

        li.querySelector('.mb-label').textContent = data.label;
        li.querySelector('.mb-kind').textContent  = data.type;
        li.querySelector('.mb-edit-label').value  = data.label;

        if (data.type === 'custom') {
            li.querySelector('.mb-edit-url').value = data.custom_url || '';
        }

        makeSortable(sub(li));
        return li;
    }

    function addItem(data) {
        root.appendChild(buildItem(data));
        refresh();
    }

    // ---- existing server-rendered items ----
    root.querySelectorAll('.mb-sub').forEach(makeSortable);
    makeSortable(root);

    // ---- clicks (delegated, so items added later work too) ----
    document.addEventListener('click', function (e) {
        var indent = e.target.closest('.mb-in');
        if (indent && !indent.disabled) {
            var li = indent.closest('.mb-item');
            var prev = li.previousElementSibling;
            if (prev) { sub(prev).appendChild(li); refresh(); }
            return;
        }

        var outdent = e.target.closest('.mb-out');
        if (outdent && !outdent.disabled) {
            var child = outdent.closest('.mb-item');
            var parentLi = child.parentElement.closest('.mb-item');
            if (parentLi) {
                parentLi.parentElement.insertBefore(child, parentLi.nextSibling);
                refresh();
            }
            return;
        }

        var toggle = e.target.closest('.mb-toggle');
        if (toggle) { toggle.closest('.mb-item').classList.toggle('is-open'); return; }

        var remove = e.target.closest('.mb-x');
        if (remove) {
            var doomed = remove.closest('.mb-item');
            // Children come back up a level rather than disappearing with it.
            var kids = sub(doomed);
            if (kids) {
                while (kids.firstElementChild) {
                    doomed.parentElement.insertBefore(kids.firstElementChild, doomed);
                }
            }
            doomed.remove();
            refresh();
        }
    });

    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('mb-edit-label')) {
            e.target.closest('.mb-item').querySelector('.mb-label').textContent = e.target.value || 'Untitled';
        }
    });

    // ---- add buttons ----
    document.querySelectorAll('[data-add-group]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var kind = btn.dataset.addGroup;
            document.querySelectorAll('[data-add="' + kind + '"]:checked').forEach(function (box) {
                addItem({ type: kind, reference_id: box.dataset.id, label: box.dataset.label });
                box.checked = false;
            });
        });
    });

    document.getElementById('mb-add-custom').addEventListener('click', function () {
        var label = document.getElementById('mb-custom-label');
        var url   = document.getElementById('mb-custom-url');
        if (!label.value.trim()) { label.focus(); return; }
        addItem({ type: 'custom', label: label.value.trim(), custom_url: url.value.trim() });
        label.value = ''; url.value = '';
    });

    document.getElementById('mb-add-home').addEventListener('click', function () {
        var label = document.getElementById('mb-home-label');
        addItem({ type: 'home', label: label.value.trim() || 'Home' });
    });

    // ---- serialise on submit ----
    function readList(list) {
        return Array.prototype.map.call(list.children, function (li) {
            var urlInput = li.querySelector(':scope > .mb-edit > .mb-edit-url');
            var kids     = sub(li);

            return {
                label:        li.querySelector(':scope > .mb-edit > .mb-edit-label').value.trim(),
                type:         li.dataset.type,
                reference_id: li.dataset.referenceId || null,
                custom_url:   urlInput ? urlInput.value.trim() : null,
                new_tab:      li.querySelector(':scope > .mb-edit > .mb-check > .mb-edit-tab').checked,
                children:     kids ? readList(kids) : []
            };
        });
    }

    form.addEventListener('submit', function () {
        field.value = JSON.stringify(readList(root));
    });

    refresh();
})();
</script>

</x-filament-panels::page>
