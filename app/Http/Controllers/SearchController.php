<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        $posts = Post::published()
            ->forCards()
            ->when($term !== '', function ($q) use ($term) {
                $q->where(function ($sub) use ($term) {
                    $sub->where('title', 'like', "%{$term}%")
                        ->orWhere('excerpt', 'like', "%{$term}%")
                        ->orWhere('body', 'like', "%{$term}%");
                });
            })
            // An empty search should show nothing rather than the whole archive.
            ->when($term === '', fn ($q) => $q->whereRaw('1 = 0'))
            ->latestFirst()
            ->paginate(12);

        return view('pages.archive', [
            'heading'   => $term !== '' ? 'খোঁজার ফল: ' . $term : 'খুঁজুন',
            'posts'     => $posts,
            'emptyText' => $term !== ''
                ? 'এই শব্দে কোনো খবর পাওয়া যায়নি।'
                : 'উপরের বাক্সে কিছু লিখে খুঁজুন।',
        ]);
    }
}
