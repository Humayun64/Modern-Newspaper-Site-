<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Creates the four pages a news site needs before it can launch.
 * They are drafts on purpose — publish them once you have written the real text.
 * AdSense will not approve a site without a privacy policy and a contact page.
 */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => 'আমাদের সম্পর্কে',
                'slug'  => 'about-us',
                'body'  => '<p>এখানে আপনার সংবাদমাধ্যম সম্পর্কে লিখুন — কবে যাত্রা শুরু, সম্পাদকীয় নীতি, এবং পাঠকের প্রতি অঙ্গীকার।</p>',
            ],
            [
                'title' => 'যোগাযোগ',
                'slug'  => 'contact',
                'body'  => '<p>সম্পাদকীয় দপ্তরের ঠিকানা, ফোন নম্বর ও ইমেইল এখানে দিন।</p>',
            ],
            [
                'title' => 'গোপনীয়তা নীতি',
                'slug'  => 'privacy-policy',
                'body'  => '<p>কোন তথ্য সংগ্রহ করা হয়, কীভাবে ব্যবহার করা হয় এবং কুকি ও বিজ্ঞাপন সম্পর্কে এখানে লিখুন।</p>',
            ],
            [
                'title' => 'ব্যবহারের শর্তাবলি',
                'slug'  => 'terms',
                'body'  => '<p>কনটেন্ট ব্যবহারের শর্ত, কপিরাইট এবং দায়বদ্ধতার সীমা এখানে লিখুন।</p>',
            ],
        ];

        foreach ($pages as $i => $data) {
            Page::firstOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, [
                    'status'         => 'draft',
                    'show_in_footer' => true,
                    'sort_order'     => $i + 1,
                ])
            );
        }
    }
}
