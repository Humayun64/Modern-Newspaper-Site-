<?php

namespace App\Filament\Admin\Pages;

use App\Models\ActivityLog;
use App\Models\User;
use Filament\Pages\Page;

class ActivityLogPage extends Page
{
    protected string $view = 'filament.admin.pages.activity-log';

    public static function getNavigationLabel(): string
    {
        return 'Activity log';
    }

    public function getTitle(): string
    {
        return 'Activity log';
    }

    public static function getNavigationSort(): ?int
    {
        return 95;
    }

    /** The log records who did what; only admins should read it. */
    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function getViewData(): array
    {
        $action = request('action');
        $userId = request('user');

        $logs = ActivityLog::query()
            ->with('user:id,name')
            ->when($action, fn ($q) => $q->where('action', $action))
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        return [
            'logs'        => $logs,
            'users'       => User::orderBy('name')->get(['id', 'name']),
            'action'      => $action,
            'userId'      => $userId,
            'failedToday' => ActivityLog::where('action', 'login_failed')
                                ->whereDate('created_at', today())->count(),
            'actions'     => [
                'login'          => 'Signed in',
                'login_failed'   => 'Failed sign-in',
                'logout'         => 'Signed out',
                'post_created'   => 'Created an article',
                'post_updated'   => 'Edited an article',
                'post_published' => 'Published an article',
                'post_deleted'   => 'Deleted an article',
                'settings_saved' => 'Changed site settings',
                'menu_saved'     => 'Changed a menu',
                'user_created'   => 'Added a user',
                'user_updated'   => 'Edited a user',
                'user_disabled'  => 'Disabled a user',
            ],
        ];
    }
}
