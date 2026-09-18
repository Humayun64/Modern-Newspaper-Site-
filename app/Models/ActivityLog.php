<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ActivityLog extends Model
{
    protected $guarded = ['id'];

    // Log rows are written once and never edited.
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Write one entry. Never throws: an audit trail failing must not take
     * down the action it was recording.
     */
    public static function record(string $action, array $attributes = []): void
    {
        try {
            $user    = $attributes['user'] ?? Auth::user();
            $request = request();

            static::create([
                'user_id'      => $user?->id,
                'user_name'    => $attributes['user_name'] ?? $user?->name,
                'action'       => $action,
                'subject_type' => $attributes['subject_type'] ?? null,
                'subject_id'   => $attributes['subject_id'] ?? null,
                'description'  => isset($attributes['description'])
                                    ? Str::limit($attributes['description'], 480)
                                    : null,
                'ip'           => $request?->ip(),
                'user_agent'   => Str::limit((string) $request?->userAgent(), 240, ''),
                'created_at'   => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** Human label for the list. */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'login'          => 'Signed in',
            'logout'         => 'Signed out',
            'login_failed'   => 'Failed sign-in',
            'post_created'   => 'Created an article',
            'post_updated'   => 'Edited an article',
            'post_published' => 'Published an article',
            'post_deleted'   => 'Deleted an article',
            'settings_saved' => 'Changed site settings',
            'menu_saved'     => 'Changed a menu',
            'user_created'   => 'Added a user',
            'user_updated'   => 'Edited a user',
            'user_disabled'  => 'Disabled a user',
            default          => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    public function getToneAttribute(): string
    {
        return match ($this->action) {
            'login_failed'               => 'critical',
            'post_deleted', 'user_disabled' => 'serious',
            'post_published', 'user_created' => 'good',
            default                      => 'neutral',
        };
    }
}
