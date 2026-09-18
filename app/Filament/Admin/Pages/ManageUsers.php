<?php

namespace App\Filament\Admin\Pages;

use App\Models\User;
use Filament\Pages\Page;

class ManageUsers extends Page
{
    protected string $view = 'filament.admin.pages.manage-users';

    public static function getNavigationLabel(): string
    {
        return 'Users';
    }

    public function getTitle(): string
    {
        return 'Users';
    }

    public static function getNavigationSort(): ?int
    {
        return 85;
    }

    /** Only admins manage accounts. */
    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function getViewData(): array
    {
        $editing = null;

        if ($id = request('edit')) {
            $editing = User::find($id);
        }

        return [
            'users'   => User::withCount('posts')->orderBy('name')->get(),
            'editing' => $editing,
        ];
    }
}
