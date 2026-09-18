<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Taken from the categories currently live on amardesh24.news.
        $categories = [
            ['name' => 'ব্রেকিং',                      'name_en' => 'Breaking',        'slug' => 'breaking',      'color' => '#d32f2f'],
            ['name' => 'শীর্ষ খবর',                    'name_en' => 'Top News',        'slug' => 'top-news',      'color' => '#c62828'],
            ['name' => 'জাতীয় সাক্ষ্য',                'name_en' => 'National',        'slug' => 'national',      'color' => '#1565c0'],
            ['name' => 'আন্তর্জাতিক সাক্ষ্য',          'name_en' => 'International',   'slug' => 'international', 'color' => '#00695c'],
            ['name' => 'যুদ্ধ',                        'name_en' => 'War',             'slug' => 'war',           'color' => '#4e342e'],
            ['name' => 'খেলাধুলা',                     'name_en' => 'Sports',          'slug' => 'sports',        'color' => '#2e7d32'],
            ['name' => 'বিনোদন',                       'name_en' => 'Entertainment',   'slug' => 'entertainment', 'color' => '#6a1b9a'],
            ['name' => 'টেক নিউজ',                     'name_en' => 'Tech',            'slug' => 'tech',          'color' => '#0277bd'],
            ['name' => 'পডকাস্ট',                      'name_en' => 'Podcast',         'slug' => 'podcast',       'color' => '#ef6c00'],
            ['name' => 'বিদেশী মিডিয়ার হাইলাইটস',      'name_en' => 'Foreign Media',   'slug' => 'foreign-media', 'color' => '#455a64'],
        ];

        foreach ($categories as $i => $data) {
            Category::updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, ['sort_order' => $i + 1, 'is_active' => true, 'show_in_menu' => true])
            );
        }
    }
}
