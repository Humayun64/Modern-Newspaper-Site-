<?php

namespace App\Filament\Admin\Pages;

use App\Models\Redirect;
use Filament\Pages\Page;

class RedirectsPage extends Page
{
    protected string $view = 'filament.admin.pages.redirects';

    public static function getNavigationLabel(): string
    {
        return 'Redirects';
    }

    public function getTitle(): string
    {
        return 'Redirects';
    }

    public static function getNavigationSort(): ?int
    {
        return 88;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function getViewData(): array
    {
        $search = trim((string) request('q'));

        return [
            'redirects' => Redirect::query()
                ->when($search !== '', fn ($q) => $q->where('from_path', 'like', "%{$search}%")
                                                    ->orWhere('to_path', 'like', "%{$search}%"))
                ->orderByDesc('hits')
                ->orderByDesc('id')
                ->paginate(25)
                ->withQueryString(),
            'search'    => $search,
            'total'     => Redirect::count(),
            'imported'  => Redirect::where('source', 'import')->count(),
            'used'      => Redirect::where('hits', '>', 0)->count(),
        ];
    }
}
