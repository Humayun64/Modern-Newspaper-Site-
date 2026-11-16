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
            $checks[] = self::check(self::PROBLEM, 3, 'Focus keyphrase',
                'No focus keyphrase set. Add the words a reader would type into Google to find this story, and the rest of the checks can be measured against it.');
        } else {
            $checks[] = self::check(self::GOOD, 3, 'Focus keyphrase',
                '“'.$phrase.'” — the rest of the checks are measured against this.');

            $checks[] = self::has($title, $phrase)
                ? self::check(self::GOOD, 3, 'Keyphrase in the headline', 'The headline contains the keyphrase.')
                : self::check(self::PROBLEM, 3, 'Keyphrase in the headline',
                    'The headline does not contain the keyphrase. The headline carries more weight than anything else, and earlier in it is better.');

            $opening = Str::limit($text, 250, '');
            $checks[] = self::has($opening, $phrase)
                ? self::check(self::GOOD, 2, 'Keyphrase in the opening', 'The keyphrase appears in the opening of the story.')
                : self::check(self::IMPROVEMENT, 2, 'Keyphrase in the opening',
                    'The first paragraph does not contain the keyphrase. Readers and search engines both read the opening first.');

            $checks[] = self::has($metaDesc, $phrase)
                ? self::check(self::GOOD, 2, 'Keyphrase in the meta description', 'The meta description contains the keyphrase.')
                : self::check(self::IMPROVEMENT, 2, 'Keyphrase in the meta description',
                    'The meta description does not contain the keyphrase. Google puts matching words in bold in the results.');

            $checks[] = self::has(rawurldecode($slug), $phrase)
                ? self::check(self::GOOD, 1, 'Keyphrase in the web address', 'The slug contains the keyphrase.')
                : self::check(self::IMPROVEMENT, 1, 'Keyphrase in the web address',
                    'The slug does not contain the keyphrase.');
        }

        // ---- length -------------------------------------------------------

        if ($words === 0) {
            $checks[] = self::check(self::PROBLEM, 3, 'Text length', 'There is no text yet.');
        } elseif ($words >= 300) {
            $checks[] = self::check(self::GOOD, 3, 'Text length',
                $words.' words — enough.');
        } elseif ($words >= 150) {
            $checks[] = self::check(self::IMPROVEMENT, 3, 'Text length',
                $words.' words. Fine for a news report, but over 300 does better in search.');
        } else {
            $checks[] = self::check(self::PROBLEM, 3, 'Text length',
                'Only '.$words.' words. A piece this short rarely surfaces in search at all.');
        }

        // ---- meta description ---------------------------------------------

        $descLen = mb_strlen($metaDesc);

        if ($descLen === 0) {
            $checks[] = self::check(self::PROBLEM, 3, 'Meta description',
                'No meta description. Without one Google picks whatever text it likes from the story.');
        } elseif ($descLen < 120) {
            $checks[] = self::check(self::IMPROVEMENT, 3, 'Meta description',
                $descLen.' characters — short. Use the space: 120–156 is the sweet spot.');
        } elseif ($descLen > 156) {
            $checks[] = self::check(self::IMPROVEMENT, 3, 'Meta description',
                $descLen.' characters — long. Google will cut the end off.');
        } else {
            $checks[] = self::check(self::GOOD, 3, 'Meta description',
                $descLen.' characters — good.');
        }

        // ---- title length ---------------------------------------------------

        $titleLen = mb_strlen($metaTitle);

        if ($titleLen === 0) {
            $checks[] = self::check(self::PROBLEM, 2, 'Title length', 'There is no title.');
        } elseif ($titleLen < 30) {
            $checks[] = self::check(self::IMPROVEMENT, 2, 'Title length',
                $titleLen.' characters — short. There is room to say more.');
        } elseif ($titleLen > 60) {
            $checks[] = self::check(self::IMPROVEMENT, 2, 'Title length',
                $titleLen.' characters — it will be cut off in the results.');
        } else {
            $checks[] = self::check(self::GOOD, 2, 'Title length',
                $titleLen.' characters — good.');
        }

        // ---- links ----------------------------------------------------------

        [$internal, $outbound] = self::countLinks($body);

        $checks[] = $internal > 0
            ? self::check(self::GOOD, 2, 'Internal links',
                $internal.' link(s) to your own pages.')
            : self::check(self::IMPROVEMENT, 2, 'Internal links',
                'No links to your own stories. Linking to related news keeps readers on the site.');

        $checks[] = $outbound > 0
            ? self::check(self::GOOD, 1, 'Outbound links',
                $outbound.' link(s) to outside sources.')
            : self::check(self::IMPROVEMENT, 1, 'Outbound links',
                'No outbound links. Citing a source makes the report more credible.');

        // ---- images -----------------------------------------------------------

        $images  = preg_match_all('/<img\b[^>]*>/i', $body, $m) ? $m[0] : [];
        $noAlt   = 0;

        foreach ($images as $img) {
            if (! preg_match('/\balt\s*=\s*("[^"]+"|\'[^\']+\')/i', $img)) {
                $noAlt++;
            }
        }

        if ($images === []) {
            $checks[] = self::check(self::IMPROVEMENT, 1, 'Images in the body',
                'No images inside the story. (The featured image is separate and not counted here.)');
        } elseif ($noAlt > 0) {
            $checks[] = self::check(self::PROBLEM, 1, 'Image alt text',
                $noAlt.' image(s) have no alt text. Without it neither Google nor a screen reader knows what the picture shows.');
        } else {
            $checks[] = self::check(self::GOOD, 1, 'Image alt text', 'Every image has alt text.');
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
