<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Redirect;

class PostController extends Controller
{
    public function show(string $slug)
    {
        $post = Post::published()
            ->with(['category', 'author', 'tags', 'categories'])
            ->where('slug', $slug)
            ->first();

        // Before giving up, check whether this used to be a real address.
        if (! $post) {
            return $this->redirectOr404($slug);
        }

        // Cheap counter: no model events, no updated_at bump.
        Post::whereKey($post->id)->increment('views');

        $related = Post::published()->forCards()
            ->where('category_id', $post->category_id)
            ->whereKeyNot($post->id)
            ->latestFirst()
            ->limit(3)
            ->get();

        $previous = Post::published()->select('id', 'title', 'slug')
            ->where('published_at', '<', $post->published_at)
            ->orderByDesc('published_at')
            ->first();

        $next = Post::published()->select('id', 'title', 'slug')
            ->where('published_at', '>', $post->published_at)
            ->orderBy('published_at')
            ->first();

        $missed = Post::published()->forCards()
            ->whereKeyNot($post->id)
            ->whereNotIn('id', $related->pluck('id'))
            ->latestFirst()
            ->limit(4)
            ->get();

        $trending = Post::published()->forCards()
            ->where('is_trending', true)
            ->orderByRaw('trending_position IS NULL, trending_position')
            ->limit(4)
            ->get();

        return view('pages.post', compact(
            'post', 'related', 'previous', 'next', 'missed', 'trending'
        ));
    }

    /** Shared by the catch-all and the fallback route. */
    public static function redirectOr404(string $path)
    {
        $redirect = Redirect::resolve($path);

        if ($redirect) {
            $redirect->recordHit();

            return redirect()->to($redirect->target_url, $redirect->status);
        }

        abort(404);
    }
}
