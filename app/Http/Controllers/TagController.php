<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;

class TagController extends Controller
{
    public function show(Tag $tag)
    {
        $posts = Post::published()
            ->forCards()
            ->whereHas('tags', fn ($q) => $q->whereKey($tag->id))
            ->latestFirst()
            ->paginate(12);

        return view('pages.archive', [
            'heading'   => 'ট্যাগ: ' . $tag->name,
            'posts'     => $posts,
            'emptyText' => 'এই ট্যাগে এখনও কোনো খবর নেই।',
        ]);
    }
}
