<?php

namespace App\Http\Controllers;

use App\Models\Page;

class PageController extends Controller
{
    public function show(Page $page)
    {
        abort_unless($page->status === 'published', 404);

        return view('pages.page', compact('page'));
    }
}
