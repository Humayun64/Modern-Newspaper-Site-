{{--
    schema.org JSON-LD.
    This is what turns a plain blue link in Google into a headline with an
    image and a date. Every field here maps to something Google actually reads.
--}}
@php
    $s        = \App\Models\Setting::all_cached();
    $siteName = $s['site_name'] ?? 'amarDesh24.news';
    $logoPath = $s['site_logo'] ?? null;
    $logo     = $logoPath
        ? (\Illuminate\Support\Str::startsWith($logoPath, ['http', '/']) ? $logoPath : asset('storage/'.$logoPath))
        : null;

    $publisher = array_filter([
        '@type' => 'Organization',
        'name'  => $siteName,
        'url'   => route('home'),
        'logo'  => $logo ? ['@type' => 'ImageObject', 'url' => $logo] : null,
    ]);

    $graph = [];

    if (! empty($post)) {
        $graph[] = array_filter([
            '@context'         => 'https://schema.org',
            '@type'            => 'NewsArticle',
            'headline'         => \Illuminate\Support\Str::limit($post->title, 110, ''),
            'description'      => $post->meta_description ?: $post->excerpt,
            'image'            => $post->featured_image ? [$post->thumb] : null,
            'datePublished'    => $post->published_at?->toAtomString(),
            'dateModified'     => ($post->updated_at ?? $post->published_at)?->toAtomString(),
            'articleSection'   => $post->category?->name,
            'inLanguage'       => 'bn',
            'author'           => $post->author
                ? ['@type' => 'Person', 'name' => $post->author->display_name]
                : $publisher,
            'publisher'        => $publisher,
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => route('post.show', $post->slug)],
        ]);

        $crumbs = [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'হোম', 'item' => route('home')],
        ];

        if ($post->category) {
            $crumbs[] = ['@type' => 'ListItem', 'position' => 2, 'name' => $post->category->name,
                         'item' => route('category.show', $post->category->slug)];
        }

        $crumbs[] = ['@type' => 'ListItem', 'position' => count($crumbs) + 1, 'name' => $post->title];

        $graph[] = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $crumbs,
        ];
    } else {
        $graph[] = [
            '@context'      => 'https://schema.org',
            '@type'         => 'WebSite',
            'name'          => $siteName,
            'url'           => route('home'),
            'inLanguage'    => 'bn',
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => ['@type' => 'EntryPoint', 'urlTemplate' => route('search').'?q={search_term_string}'],
                'query-input' => 'required name=search_term_string',
            ],
        ];

        $graph[] = array_merge(['@context' => 'https://schema.org'], $publisher);
    }
@endphp

@foreach ($graph as $block)
<script type="application/ld+json">{!! json_encode($block, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endforeach
