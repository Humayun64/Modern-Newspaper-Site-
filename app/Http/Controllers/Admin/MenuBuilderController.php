<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuBuilderController extends Controller
{
    protected const TYPES = ['home', 'category', 'page', 'custom'];

    /** Two levels is what the navigation renders: top item plus a dropdown. */
    protected const MAX_DEPTH = 2;

    public function save(Request $request)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $location = in_array($request->input('location'), ['header', 'footer'], true)
            ? $request->input('location')
            : 'header';

        $menu = Menu::firstOrCreate(
            ['location' => $location],
            ['name' => $location === 'header' ? 'Main navigation' : 'Footer links']
        );

        $items = json_decode($request->input('items', '[]'), true);

        if (! is_array($items)) {
            return back()->withErrors(['items' => 'The menu could not be read. Nothing was changed.']);
        }

        // Rebuilt in one transaction: a half-saved menu is worse than no save.
        DB::transaction(function () use ($menu, $items) {
            MenuItem::where('menu_id', $menu->id)->delete();
            $this->store($menu, $items, null, 1);
        });

        Menu::forget($location);

        ActivityLog::record('menu_saved', ['description' => ucfirst($location).' menu']);

        return back()->with('menu_saved', true);
    }

    protected function store(Menu $menu, array $items, ?int $parentId, int $depth): void
    {
        foreach (array_values($items) as $i => $item) {
            if (! is_array($item)) {
                continue;
            }

            $label = trim((string) ($item['label'] ?? ''));

            if ($label === '') {
                continue;
            }

            $type = in_array($item['type'] ?? '', self::TYPES, true) ? $item['type'] : 'custom';

            $row = MenuItem::create([
                'menu_id'      => $menu->id,
                'parent_id'    => $parentId,
                'label'        => mb_substr($label, 0, 120),
                'type'         => $type,
                'reference_id' => $type === 'category' || $type === 'page'
                                    ? ((int) ($item['reference_id'] ?? 0) ?: null)
                                    : null,
                'custom_url'   => $type === 'custom' ? mb_substr((string) ($item['custom_url'] ?? ''), 0, 500) : null,
                'target'       => ! empty($item['new_tab']) ? '_blank' : null,
                'sort_order'   => $i,
            ]);

            if (! empty($item['children']) && is_array($item['children']) && $depth < self::MAX_DEPTH) {
                $this->store($menu, $item['children'], $row->id, $depth + 1);
            }
        }
    }
}
