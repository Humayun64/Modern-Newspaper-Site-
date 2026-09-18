@foreach ($nodes as $node)
    <li class="mb-item"
        data-type="{{ $node['type'] }}"
        data-reference-id="{{ $node['reference_id'] }}"
        data-new-tab="{{ $node['new_tab'] ? '1' : '' }}">

        <div class="mb-head">
            <span class="mb-grip" aria-hidden="true">&#10287;</span>
            <span class="mb-name">
                <span class="mb-label">{{ $node['label'] }}</span>
                <span class="mb-kind">{{ $node['type'] }}</span>
            </span>
            <button type="button" class="mb-step mb-out" title="Move out one level">&larr;</button>
            <button type="button" class="mb-step mb-in" title="Make it a sub-item of the one above">&rarr;</button>
            <button type="button" class="mb-toggle">Edit</button>
            <button type="button" class="mb-x" aria-label="Remove">&times;</button>
        </div>

        <div class="mb-edit">
            <input class="mb-input mb-edit-label" value="{{ $node['label'] }}" placeholder="Link text">
            @if ($node['type'] === 'custom')
                <input class="mb-input mb-edit-url" value="{{ $node['custom_url'] }}" placeholder="https://…">
            @endif
            <label class="mb-check">
                <input type="checkbox" class="mb-edit-tab" @checked($node['new_tab'])> Open in a new tab
            </label>
        </div>

        <ul class="mb-sub">
            @include('filament.admin.pages.partials.menu-nodes', ['nodes' => $node['children']])
        </ul>
    </li>
@endforeach
