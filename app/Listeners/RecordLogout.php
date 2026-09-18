<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Logout;

class RecordLogout
{
    public function handle(Logout $event): void
    {
        if ($event->user) {
            ActivityLog::record('logout', ['user' => $event->user]);
        }
    }
}
