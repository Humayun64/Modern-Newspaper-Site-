<?php

use App\Http\Controllers\Admin\MenuBuilderController;
use App\Http\Controllers\Admin\RedirectController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

// ---- admin ----------------------------------------------------------------
Route::middleware('auth')->group(function () {
    Route::post('/admin/settings/save', [SettingsController::class, 'save'])->name('admin.settings.save');
    Route::post('/admin/menus/save', [MenuBuilderController::class, 'save'])->name('admin.menu.save');
    Route::post('/admin/users/save', [UserController::class, 'save'])->name('admin.users.save');
    Route::post('/admin/users/{user}/toggle', [UserController::class, 'toggle'])->name('admin.users.toggle');
    Route::post('/admin/redirects', [RedirectController::class, 'store'])->name('admin.redirects.store');
    Route::delete('/admin/redirects/{redirect}', [RedirectController::class, 'destroy'])->name('admin.redirects.destroy');
});

// ---- feeds and sitemaps ---------------------------------------------------
// Declared before the catch-all so /sitemap.xml is never mistaken for a slug.
Route::get('/robots.txt', [FeedController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [FeedController::class, 'sitemapIndex'])->name('sitemap.index');
Route::get('/sitemap-posts-{page}.xml', [FeedController::class, 'sitemapPosts'])
    ->whereNumber('page')->name('sitemap.posts');
Route::get('/sitemap-categories.xml', [FeedController::class, 'sitemapCategories'])->name('sitemap.categories');
Route::get('/sitemap-pages.xml', [FeedController::class, 'sitemapPages'])->name('sitemap.pages');
Route::get('/sitemap-news.xml', [FeedController::class, 'sitemapNews'])->name('sitemap.news');

Route::get('/feed', [FeedController::class, 'rss'])->name('rss');
Route::get('/feed/{category}', [FeedController::class, 'rss'])->name('rss.category');

// ---- public ---------------------------------------------------------------
Route::get('/', HomeController::class)->name('home');

Route::get('/search', SearchController::class)->name('search');
Route::get('/category/{category}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/tag/{tag}', [TagController::class, 'show'])->name('tag.show');

// Pages sit under /page/ rather than the site root. Sharing the root with post
// slugs would mean a page and an article could claim the same URL.
Route::get('/page/{page}', [PageController::class, 'show'])->name('page.show');

// Keep this last: it is a catch-all for post slugs at the site root,
// so any route added below it would never be reached.
Route::get('/{slug}', [PostController::class, 'show'])->name('post.show');

// Old multi-segment addresses land here, where the redirect table turns them
// into a 301 rather than a 404.
Route::fallback(fn () => PostController::redirectOr404(request()->path()));
