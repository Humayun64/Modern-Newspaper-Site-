<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Failed;

class RecordFailedLogin
{
    public function handle(Failed $event): void
    {
        // No user object on a failed attempt, so record the email that was
        // tried. Repeated rows from one IP are what a break-in attempt looks like.
        ActivityLog::record('login_failed', [
            'user'        => $event->user,
            'user_name'   => $event->credentials['email'] ?? 'unknown',
            'description' => 'Attempted with: ' . ($event->credentials['email'] ?? 'unknown'),
        ]);
    }
}
