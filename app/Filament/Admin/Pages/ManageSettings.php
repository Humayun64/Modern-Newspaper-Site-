<?php

namespace App\Filament\Admin\Pages;

use App\Models\Setting;
use Filament\Pages\Page;

/**
 * Site settings screen.
 *
 * The form is plain HTML posting to SettingsController, rather than Filament's
 * form builder. Filament's schema API moves between major versions; a normal
 * form does not, and this screen needs to keep working through upgrades.
 */
class ManageSettings extends Page
{
    protected string $view = 'filament.admin.pages.manage-settings';

    public static function getNavigationLabel(): string
    {
        return 'Site settings';
    }

    public function getTitle(): string
    {
        return 'Site settings';
    }

    public static function getNavigationSort(): ?int
    {
        return 90;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function getViewData(): array
    {
        return ['settings' => Setting::all_cached()];
    }
}
