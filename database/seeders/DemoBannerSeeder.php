<?php

namespace Database\Seeders;

use App\Models\Ad;
use Illuminate\Database\Seeder;

/**
 * Creates placeholder banners so the ad slots are visible while building.
 * Replace the image from Admin → Ads; nothing here is permanent.
 */
class DemoBannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'name'      => 'Header banner (demo)',
                'placement' => 'header',
                'image'     => '/images/demo-banner.svg',
                'link'      => '',
            ],
            [
                'name'      => 'Sidebar banner (demo)',
                'placement' => 'sidebar_top',
                'image'     => '/images/demo-banner.svg',
                'link'      => '',
            ],
        ];

        foreach ($banners as $i => $data) {
            Ad::firstOrCreate(
                ['name' => $data['name']],
                array_merge($data, [
                    'is_active'  => true,
                    'sort_order' => $i + 1,
                ])
            );
        }
    }
}
