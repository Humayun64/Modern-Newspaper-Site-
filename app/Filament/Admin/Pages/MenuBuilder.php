<?php

namespace App\Filament\Admin\Pages;

use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page as ContentPage;
use Filament\Pages\Page;

/**
 * Drag-and-drop menu builder.
 *
 * Plain HTML plus SortableJS posting to MenuBuilderController, for the same
 * reason as the settings screen: no dependency on Filament's form API.
 */
class MenuBuilder extends Page
{
    protected string $view = 'filament.admin.pages.menu-builder';

    public static function getNavigationLabel(): string
    {
        return 'Menus';
    }

    public function getTitle(): string
    {
        return 'Menus';
    }

    public static function getNavigationSort(): ?int
    {
        return 80;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function getViewData(): array
    {
        $location = in_array(request('menu'), ['header', 'footer'], true)
            ? request('menu')
            : 'header';

        $menu = Menu::firstOrCreate(
            ['location' => $location],
            ['name' => $location === 'header' ? 'Main navigation' : 'Footer links']
        );

        $items = MenuItem::where('menu_id', $menu->id)->orderBy('sort_order')->get();

        return [
            'location'   => $location,
            'menu'       => $menu,
            'tree'       => $this->tree($items, null),
            'categories' => Category::active()->get(['id', 'name']),
            'pages'      => ContentPage::orderBy('sort_order')->get(['id', 'title', 'status']),
        ];
    }

    /** Nest the flat rows so the view can render the saved order. */
    protected function tree($items, ?int $parentId): array
    {
        $out = [];

        foreach ($items->where('parent_id', $parentId) as $item) {
            $out[] = [
                'id'           => $item->id,
                'label'        => $item->label,
                'type'         => $item->type,
                'reference_id' => $item->reference_id,
                'custom_url'   => $item->custom_url,
                'new_tab'      => $item->target === '_blank',
                'children'     => $this->tree($items, $item->id),
            ];
        }

        return $out;
    }
}
