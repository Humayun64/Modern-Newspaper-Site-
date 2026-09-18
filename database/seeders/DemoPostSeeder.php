<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Support\BanglaSlug;
use Illuminate\Database\Seeder;

class DemoPostSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::first();

        $tags = collect([
            'বাংলাদেশ', 'ঢাকা', 'অর্থনীতি', 'শিক্ষা', 'স্বাস্থ্য',
            'প্রযুক্তি', 'ক্রিকেট', 'ফুটবল', 'আবহাওয়া', 'পরিবহন',
        ])->map(fn ($name) => Tag::firstOrCreate(['name' => $name]));

        $data = [
            'breaking' => [
                ['রাজধানীতে টানা বৃষ্টি, বেশ কয়েকটি এলাকায় জলাবদ্ধতা', 'standard'],
                ['ঢাকা-চট্টগ্রাম মহাসড়কে যান চলাচল স্বাভাবিক হয়েছে', 'standard'],
                ['সারাদেশে আগামীকাল থেকে তাপমাত্রা কমার পূর্বাভাস', 'standard'],
                ['নতুন সেতু চালু হওয়ায় কমল যাত্রাপথের সময়', 'standard'],
            ],
            'top-news' => [
                ['চলতি অর্থবছরে রপ্তানি আয়ে প্রবৃদ্ধি', 'standard'],
                ['সরকারি সেবা ডিজিটাল করার নতুন উদ্যোগ ঘোষণা', 'standard'],
                ['শিক্ষাপ্রতিষ্ঠানে নতুন পাঠ্যক্রম বাস্তবায়ন শুরু', 'standard'],
            ],
            'national' => [
                ['দেশের বিভিন্ন জেলায় বৃক্ষরোপণ কর্মসূচি শুরু', 'standard'],
                ['গ্রামীণ সড়ক উন্নয়নে নতুন প্রকল্পের অনুমোদন', 'standard'],
                ['কৃষি খাতে আধুনিক যন্ত্রপাতি ব্যবহারে আগ্রহ বাড়ছে', 'standard'],
                ['নদী ভাঙন রোধে স্থায়ী বাঁধ নির্মাণের দাবি', 'standard'],
            ],
            'international' => [
                ['জলবায়ু সম্মেলনে নতুন প্রতিশ্রুতি দিল অংশগ্রহণকারী দেশগুলো', 'standard'],
                ['বিশ্ব অর্থনীতিতে ধীরগতির পূর্বাভাস দিল আন্তর্জাতিক সংস্থা', 'standard'],
                ['দক্ষিণ এশিয়ায় বাণিজ্য সহযোগিতা বাড়ানোর আহ্বান', 'standard'],
            ],
            'sports' => [
                ['ঘরের মাঠে সিরিজ জয় বাংলাদেশের', 'standard'],
                ['তরুণ ক্রিকেটারদের নিয়ে নতুন প্রশিক্ষণ ক্যাম্প', 'standard'],
                ['ফুটবল লিগের সময়সূচি প্রকাশ', 'video'],
                ['দেশের প্রথম নারী ফুটবল একাডেমি উদ্বোধন', 'standard'],
            ],
            'tech' => [
                ['দেশে চালু হলো নতুন ব্রডব্যান্ড সেবা', 'standard'],
                ['কৃত্রিম বুদ্ধিমত্তা নিয়ে তরুণদের আগ্রহ বাড়ছে', 'standard'],
                ['স্মার্টফোন বাজারে নতুন মডেলের দাম কমেছে', 'standard'],
            ],
            'entertainment' => [
                ['নতুন চলচ্চিত্রের শুটিং শুরু আগামী মাসে', 'standard'],
                ['সংগীত উৎসবে দর্শকের ঢল', 'video'],
            ],
            'war' => [
                ['সীমান্ত এলাকায় শান্তি আলোচনার উদ্যোগ', 'standard'],
                ['ত্রাণ সহায়তা পৌঁছেছে ক্ষতিগ্রস্ত এলাকায়', 'standard'],
            ],
            'podcast' => [
                ['পডকাস্ট: অর্থনীতির চলতি হালচাল নিয়ে আলোচনা', 'podcast'],
                ['পডকাস্ট: তরুণ উদ্যোক্তাদের সাফল্যের গল্প', 'podcast'],
            ],
            'foreign-media' => [
                ['আন্তর্জাতিক গণমাধ্যমে বাংলাদেশের অর্থনীতি নিয়ে প্রতিবেদন', 'standard'],
                ['বিদেশি সংবাদমাধ্যমে দেশের পর্যটন খাতের প্রশংসা', 'standard'],
            ],
        ];

        $body = $this->sampleBody();
        $i = 0;

        foreach ($data as $categorySlug => $items) {
            $category = Category::where('slug', $categorySlug)->first();

            if (! $category) {
                continue;
            }

            foreach ($items as [$title, $type]) {
                $i++;

                $post = Post::updateOrCreate(
                    ['slug' => BanglaSlug::make($title)],
                    [
                        'title'             => $title,
                        'body'              => $body,
                        'featured_image'    => "https://picsum.photos/seed/ad24{$i}/900/600",
                        'image_caption'     => 'প্রতীকী ছবি',
                        'category_id'       => $category->id,
                        'author_id'         => $author->id,
                        'type'              => $type,
                        'video_url'         => $type === 'video' ? 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' : null,
                        'status'            => 'published',
                        'published_at'      => now()->subHours($i * 7)->subMinutes(rand(0, 59)),
                        'views'             => rand(50, 9000),
                        'is_breaking'       => $categorySlug === 'breaking',
                        'is_featured'       => $i % 6 === 1,
                        'is_editors_pick'   => $i % 9 === 2,
                        'is_trending'       => $i <= 5,
                        'trending_position' => $i <= 5 ? $i : null,
                    ]
                );

                $post->categories()->syncWithoutDetaching([$category->id]);
                $post->tags()->syncWithoutDetaching($tags->random(rand(2, 3))->pluck('id')->all());
            }
        }

        Tag::all()->each(fn (Tag $t) => $t->update(['posts_count' => $t->posts()->count()]));

        $this->command->info('Demo posts created: ' . Post::count());
    }

    protected function sampleBody(): string
    {
        $para = 'এটি একটি নমুনা সংবাদ প্রতিবেদন। প্রকৃত সংবাদ প্রকাশের আগে এই লেখাটি সরিয়ে ফেলা হবে। '
              . 'সংবাদের মূল অংশে সাধারণত ঘটনার বিবরণ, সংশ্লিষ্ট ব্যক্তিদের বক্তব্য এবং প্রাসঙ্গিক তথ্য থাকে। '
              . 'পাঠকের সুবিধার্থে অনুচ্ছেদগুলো ছোট রাখা হয় এবং গুরুত্বপূর্ণ তথ্য শুরুতেই উপস্থাপন করা হয়।';

        return "<p>{$para}</p>\n<p>{$para}</p>\n<h2>প্রেক্ষাপট</h2>\n<p>{$para}</p>\n<p>{$para}</p>";
    }
}