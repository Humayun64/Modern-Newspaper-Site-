<?php

namespace App\Filament\Admin\Pages;

use App\Models\GlobalCoverage;
use Filament\Pages\Page;

/**
 * Global coverage — foreign-media highlights with a PDF (correction 20).
 *
 * Plain HTML form posting to GlobalCoverageController, like the other custom
 * screens here. Filament's schema API moves between major versions; an
 * ordinary form does not, and this needs to survive upgrades.
 */
class GlobalCoveragePage extends Page
{
    protected string $view = 'filament.admin.pages.global-coverage';

    public static function getNavigationLabel(): string
    {
        return 'Global coverage';
    }

    public function getTitle(): string
    {
        return 'Global coverage';
    }

    public static function getNavigationSort(): ?int
    {
        return 70;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function getViewData(): array
    {
        return [
            'items' => GlobalCoverage::orderBy('sort_order')->orderByDesc('id')->get(),
        ];
    }
}
