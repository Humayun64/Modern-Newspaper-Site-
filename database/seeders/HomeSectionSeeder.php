<?php

namespace Database\Seeders;

use App\Models\HomeSection;
use Illuminate\Database\Seeder;

class HomeSectionSeeder extends Seeder
{
    public function run(): void
    {
        // The homepage blocks, in the order the MoreNews "General News" demo
        // shows them. Reorder or switch any of these off from admin later
        // instead of editing Blade.
        $sections = [
            ['layout' => 'main_news',           'title' => 'প্রধান সংবাদ',       'source' => 'featured',     'limit' => 5],
            ['layout' => 'editors_pick',        'title' => 'সম্পাদকের পছন্দ',    'source' => 'editors_pick', 'limit' => 2],
            ['layout' => 'latest_popular',      'title' => null,                 'source' => 'latest',       'limit' => 4],
            ['layout' => 'featured_posts',      'title' => 'নির্বাচিত সংবাদ',    'source' => 'latest',       'limit' => 4],
            ['layout' => 'double_columns',      'title' => 'জাতীয়',              'source' => 'category',     'limit' => 3],
            ['layout' => 'double_columns',      'title' => 'আন্তর্জাতিক',        'source' => 'category',     'limit' => 3],
            ['layout' => 'posts_slider',        'title' => null,                 'source' => 'latest',       'limit' => 5],
            ['layout' => 'posts_grid',          'title' => 'সব খবর',             'source' => 'latest',       'limit' => 6],
            ['layout' => 'express_list',        'title' => 'খেলাধুলা',           'source' => 'category',     'limit' => 5],
            ['layout' => 'posts_list',          'title' => 'আরও খবর',            'source' => 'latest',       'limit' => 6],
            ['layout' => 'trending',            'title' => 'ট্রেন্ডিং',          'source' => 'trending',     'limit' => 5],
            ['layout' => 'you_may_have_missed', 'title' => 'যা মিস করেছেন',      'source' => 'popular',      'limit' => 4],
        ];

        foreach ($sections as $i => $data) {
            HomeSection::updateOrCreate(
                ['layout' => $data['layout'], 'title' => $data['title']],
                array_merge($data, ['sort_order' => $i + 1, 'is_active' => true])
            );
        }
    }
}
