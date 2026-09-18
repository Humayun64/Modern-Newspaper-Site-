<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;

class CategoryController extends Controller
{
    public function show(Category $category)
    {
        abort_unless($category->is_active, 404);

        $posts = Post::published()
            ->forCards()
            ->inCategory($category)
            ->latestFirst()
            ->paginate(12);

        return view('pages.archive', [
            'heading'     => $category->name,
            'description' => $category->meta_description ?: $category->description,
            'posts'       => $posts,
            'emptyText'   => 'এই বিভাগে এখনও কোনো খবর প্রকাশ করা হয়নি।',
        ]);
    }
}
