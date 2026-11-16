<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * The SEO check that sits under the article form.
 *
 * It is deliberately a plain class with one public method and no state: the
 * form calls it on every re-render, so it has to be cheap and it must never
 * touch the database or the network. Everything it knows comes from the
 * fields the writer has filled in.
 *
 * A word on the thresholds. They are the ones the SEO tools have settled on
 * for English, and this site is in Bangla, where a complete news report is
 * often 250 words. So the length check marks 300 as the target but treats
 * anything over 150 as an improvement rather than a problem — a short news
 * story is not a broken story, and a panel that shows red on every ordinary
 * report would teach the newsroom to ignore it.
 */
class SeoAnalyzer
{
    public const GOOD        = 'good';
    public const IMPROVEMENT = 'improvement';
    public const PROBLEM     = 'problem';

    /**
     * @param  array{title?:string,body?:string,slug?:string,meta_title?:string,meta_description?:string,focus_keyphrase?:string,excerpt?:string}  $data
     * @return array{score:int,checks:array<int,array{status:string,label:string,text:string,weight:int}>}
     */
    public static function analyze(array $data): array
    {
        $title     = trim((string) ($data['title'] ?? ''));
        $body      = (string) ($data['body'] ?? '');
        $slug      = trim((string) ($data['slug'] ?? ''));
        $metaTitle = trim((string) ($data['meta_title'] ?? '')) ?: $title;
        $metaDesc  = trim((string) ($data['meta_description'] ?? '')) ?: trim((string) ($data['excerpt'] ?? ''));
        $phrase    = trim((string) ($data['focus_keyphrase'] ?? ''));

        $text  = trim(preg_replace('/\s+/u', ' ', strip_tags($body)));
        $words = $text === '' ? 0 : count(preg_split('/\s+/u', $text));

        $checks = [];

        // ---- the keyphrase ------------------------------------------------

        if ($phrase === '') {
            $checks[] = self::check(self::PROBLEM, 3, 'ফোকাস কিওয়ার্ড',
                'কোনো ফোকাস কিওয়ার্ড দেওয়া হয়নি। যে শব্দে পাঠক এই খবরটি খুঁজবে সেটি লিখুন — তারপর বাকি পরীক্ষাগুলো সেটির সাথে মিলিয়ে দেখা হবে।');
        } else {
            $checks[] = self::check(self::GOOD, 3, 'ফোকাস কিওয়ার্ড',
                '“'.$phrase.'” — এটি ধরে বাকি পরীক্ষাগুলো করা হয়েছে।');

            $checks[] = self::has($title, $phrase)
                ? self::check(self::GOOD, 3, 'শিরোনামে কিওয়ার্ড', 'শিরোনামে কিওয়ার্ডটি আছে।')
                : self::check(self::PROBLEM, 3, 'শিরোনামে কিওয়ার্ড',
                    'শিরোনামে কিওয়ার্ডটি নেই। শিরোনামই সবচেয়ে বেশি ওজন বহন করে — শুরুর দিকে রাখতে পারলে ভালো।');

            $opening = Str::limit($text, 250, '');
            $checks[] = self::has($opening, $phrase)
                ? self::check(self::GOOD, 2, 'শুরুতে কিওয়ার্ড', 'লেখার প্রথম অংশেই কিওয়ার্ডটি এসেছে।')
                : self::check(self::IMPROVEMENT, 2, 'শুরুতে কিওয়ার্ড',
                    'প্রথম অনুচ্ছেদে কিওয়ার্ডটি নেই। পাঠক ও সার্চ ইঞ্জিন দুজনেই শুরুটা আগে পড়ে।');

            $checks[] = self::has($metaDesc, $phrase)
                ? self::check(self::GOOD, 2, 'মেটা বর্ণনায় কিওয়ার্ড', 'মেটা বর্ণনায় কিওয়ার্ডটি আছে।')
                : self::check(self::IMPROVEMENT, 2, 'মেটা বর্ণনায় কিওয়ার্ড',
                    'মেটা বর্ণনায় কিওয়ার্ডটি নেই। Google সার্চ ফলাফলে মিলে যাওয়া শব্দ মোটা করে দেখায়।');

            $checks[] = self::has(rawurldecode($slug), $phrase)
                ? self::check(self::GOOD, 1, 'ঠিকানায় কিওয়ার্ড', 'ওয়েব ঠিকানায় কিওয়ার্ডটি আছে।')
                : self::check(self::IMPROVEMENT, 1, 'ঠিকানায় কিওয়ার্ড',
                    'ওয়েব ঠিকানায় কিওয়ার্ডটি নেই।');
        }

        // ---- length -------------------------------------------------------

        if ($words === 0) {
            $checks[] = self::check(self::PROBLEM, 3, 'লেখার দৈর্ঘ্য', 'এখনো কোনো লেখা নেই।');
        } elseif ($words >= 300) {
            $checks[] = self::check(self::GOOD, 3, 'লেখার দৈর্ঘ্য',
                Bangla::digits((string) $words).' শব্দ — যথেষ্ট।');
        } elseif ($words >= 150) {
            $checks[] = self::check(self::IMPROVEMENT, 3, 'লেখার দৈর্ঘ্য',
                Bangla::digits((string) $words).' শব্দ। সংবাদ হিসেবে চলে, তবে ৩০০ শব্দের বেশি হলে সার্চে ভালো করে।');
        } else {
            $checks[] = self::check(self::PROBLEM, 3, 'লেখার দৈর্ঘ্য',
                'মাত্র '.Bangla::digits((string) $words).' শব্দ। এত ছোট লেখা সার্চে প্রায় আসে না।');
        }

        // ---- meta description ---------------------------------------------

        $descLen = mb_strlen($metaDesc);

        if ($descLen === 0) {
            $checks[] = self::check(self::PROBLEM, 3, 'মেটা বর্ণনা',
                'মেটা বর্ণনা লেখা হয়নি। না লিখলে Google নিজেই লেখা থেকে যেকোনো অংশ তুলে নেবে।');
        } elseif ($descLen < 120) {
            $checks[] = self::check(self::IMPROVEMENT, 3, 'মেটা বর্ণনা',
                Bangla::digits((string) $descLen).' অক্ষর — ছোট। ১২০–১৫৬ অক্ষরে জায়গাটা কাজে লাগান।');
        } elseif ($descLen > 156) {
            $checks[] = self::check(self::IMPROVEMENT, 3, 'মেটা বর্ণনা',
                Bangla::digits((string) $descLen).' অক্ষর — বড়। Google শেষটা কেটে দেবে।');
        } else {
            $checks[] = self::check(self::GOOD, 3, 'মেটা বর্ণনা',
                Bangla::digits((string) $descLen).' অক্ষর — ঠিক আছে।');
        }

        // ---- title length ---------------------------------------------------

        $titleLen = mb_strlen($metaTitle);

        if ($titleLen === 0) {
            $checks[] = self::check(self::PROBLEM, 2, 'শিরোনামের দৈর্ঘ্য', 'শিরোনাম নেই।');
        } elseif ($titleLen < 30) {
            $checks[] = self::check(self::IMPROVEMENT, 2, 'শিরোনামের দৈর্ঘ্য',
                Bangla::digits((string) $titleLen).' অক্ষর — ছোট। আরও কিছু যোগ করার জায়গা আছে।');
        } elseif ($titleLen > 60) {
            $checks[] = self::check(self::IMPROVEMENT, 2, 'শিরোনামের দৈর্ঘ্য',
                Bangla::digits((string) $titleLen).' অক্ষর — সার্চ ফলাফলে কেটে যাবে।');
        } else {
            $checks[] = self::check(self::GOOD, 2, 'শিরোনামের দৈর্ঘ্য',
                Bangla::digits((string) $titleLen).' অক্ষর — ঠিক আছে।');
        }

        // ---- links ----------------------------------------------------------

        [$internal, $outbound] = self::countLinks($body);

        $checks[] = $internal > 0
            ? self::check(self::GOOD, 2, 'ভেতরের লিংক',
                Bangla::digits((string) $internal).'টি লিংক নিজের সাইটের দিকে।')
            : self::check(self::IMPROVEMENT, 2, 'ভেতরের লিংক',
                'নিজের সাইটের কোনো খবরের লিংক নেই। সম্পর্কিত খবরে লিংক দিলে পাঠক সাইটেই থাকে।');

        $checks[] = $outbound > 0
            ? self::check(self::GOOD, 1, 'বাইরের লিংক',
                Bangla::digits((string) $outbound).'টি লিংক বাইরের উৎসের দিকে।')
            : self::check(self::IMPROVEMENT, 1, 'বাইরের লিংক',
                'কোনো বাইরের উৎসের লিংক নেই। তথ্যের উৎস দিলে খবরটি বিশ্বাসযোগ্য হয়।');

        // ---- images -----------------------------------------------------------

        $images  = preg_match_all('/<img\b[^>]*>/i', $body, $m) ? $m[0] : [];
        $noAlt   = 0;

        foreach ($images as $img) {
            if (! preg_match('/\balt\s*=\s*("[^"]+"|\'[^\']+\')/i', $img)) {
                $noAlt++;
            }
        }

        if ($images === []) {
            $checks[] = self::check(self::IMPROVEMENT, 1, 'লেখার ভেতরের ছবি',
                'লেখার ভেতরে কোনো ছবি নেই। (ফিচার ছবি আলাদা — সেটি এই হিসাবের বাইরে।)');
        } elseif ($noAlt > 0) {
            $checks[] = self::check(self::PROBLEM, 1, 'ছবির alt লেখা',
                Bangla::digits((string) $noAlt).'টি ছবিতে alt লেখা নেই। alt ছাড়া ছবি Google-ও বোঝে না, পর্দা-পাঠকও না।');
        } else {
            $checks[] = self::check(self::GOOD, 1, 'ছবির alt লেখা', 'সব ছবিতে alt লেখা আছে।');
        }

        // ---- score ------------------------------------------------------------

        $earned = 0;
        $total  = 0;

        foreach ($checks as $c) {
            $total  += $c['weight'];
            $earned += match ($c['status']) {
                self::GOOD        => $c['weight'],
                self::IMPROVEMENT => $c['weight'] / 2,
                default           => 0,
            };
        }

        return [
            'score'  => $total > 0 ? (int) round($earned / $total * 100) : 0,
            'checks' => $checks,
            'words'  => $words,
        ];
    }

    /** Case-insensitive, whitespace-tolerant match. Bangla has no case, but the slug and any English in it do. */
    protected static function has(string $haystack, string $needle): bool
    {
        $haystack = preg_replace('/\s+/u', ' ', mb_strtolower($haystack));
        $needle   = preg_replace('/\s+/u', ' ', mb_strtolower(trim($needle)));

        if ($needle === '') {
            return false;
        }

        // A slug joins words with hyphens, so compare that shape too.
        return str_contains($haystack, $needle)
            || str_contains($haystack, str_replace(' ', '-', $needle));
    }

    /** @return array{0:int,1:int} internal, outbound */
    protected static function countLinks(string $body): array
    {
        if (! preg_match_all('/<a\b[^>]*href\s*=\s*("[^"]*"|\'[^\']*\')/i', $body, $m)) {
            return [0, 0];
        }

        $host = parse_url((string) config('app.url'), PHP_URL_HOST);

        $internal = 0;
        $outbound = 0;

        foreach ($m[1] as $raw) {
            $href = trim($raw, '"\'');

            if ($href === '' || Str::startsWith($href, ['#', 'mailto:', 'tel:', 'javascript:'])) {
                continue;
            }

            if (Str::startsWith($href, '/')) {
                $internal++;
                continue;
            }

            $linkHost = parse_url($href, PHP_URL_HOST);

            if ($linkHost === null) {
                $internal++;          // a relative path
            } elseif ($host && str_contains($linkHost, (string) $host)) {
                $internal++;
            } else {
                $outbound++;
            }
        }

        return [$internal, $outbound];
    }

    /** @return array{status:string,weight:int,label:string,text:string} */
    protected static function check(string $status, int $weight, string $label, string $text): array
    {
        return compact('status', 'weight', 'label', 'text');
    }
}
