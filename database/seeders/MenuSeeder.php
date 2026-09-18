<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Creates the header and footer menus, pre-filled from what already exists,
 * so the menu builder opens with something to drag rather than a blank page.
 */
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $header = Menu::firstOrCreate(['location' => 'header'], ['name' => 'Main navigation']);
        $footer = Menu::firstOrCreate(['location' => 'footer'], ['name' => 'Footer links']);

        if ($header->items()->doesntExist()) {
            MenuItem::create([
                'menu_id' => $header->id, 'label' => 'হোম', 'type' => 'home', 'sort_order' => 0,
            ]);

            foreach (Category::inMenu()->limit(8)->get() as $i => $cat) {
                MenuItem::create([
                    'menu_id'      => $header->id,
                    'label'        => $cat->name,
                    'type'         => 'category',
                    'reference_id' => $cat->id,
                    'sort_order'   => $i + 1,
                ]);
            }
        }

        if ($footer->items()->doesntExist()) {
            foreach (Page::orderBy('sort_order')->get() as $i => $page) {
                MenuItem::create([
                    'menu_id'      => $footer->id,
                    'label'        => $page->title,
                    'type'         => 'page',
                    'reference_id' => $page->id,
                    'sort_order'   => $i,
                ]);
            }
        }

        Menu::forget('header');
        Menu::forget('footer');
    }
}
