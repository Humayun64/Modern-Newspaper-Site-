<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seeds the settings keys the site reads.
 *
 * Uses firstOrCreate, not updateOrCreate: re-running the seeder adds any new
 * keys without wiping values you have already changed in the admin panel.
 */
class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'general' => [
                'site_name'      => 'amarDesh24.news',
                'site_tagline'   => 'Bangladesh',
                'site_logo'      => '',   // upload from admin; falls back to the text name
                'site_favicon'   => '',
                'site_email'     => 'admin@amardesh24.news',
                'site_address'   => 'বাড়ি-১৩/বি, রোড-৮, লেক সাইড, বারিধারা ডিওএইচএস, ঢাকা, বাংলাদেশ',
                'about_text'     => 'আমারদেশ২৪.নিউজ একটি বিশ্বস্ত ও নির্ভরযোগ্য অনলাইন সংবাদমাধ্যম, যা দ্রুত সময়ে সঠিক ও সর্বশেষ খবর পাঠকদের কাছে পৌঁছে দেয়।',
                'posts_per_page' => '12',
                'nav_cta_text'   => 'Watch Videos',
                'nav_cta_url'    => '',
            ],
            'social' => [
                'facebook_url'  => '',
                'twitter_url'   => '',
                'linkedin_url'  => '',
                'vk_url'        => '',
                'youtube_url'   => '',
                'instagram_url' => '',
            ],
            'seo' => [
                'meta_title'       => 'amarDesh24.news',
                'meta_description' => '',
                'google_analytics' => '',
            ],
        ];

        foreach ($settings as $group => $items) {
            foreach ($items as $key => $value) {
                Setting::firstOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
            }
        }
    }
}
