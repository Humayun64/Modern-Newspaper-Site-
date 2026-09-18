<?php

namespace App\Models;

use App\Support\BanglaSlug;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Implements FilamentUser deliberately.
 *
 * Without this contract Filament only lets people into the panel when
 * APP_ENV is local — in production it refuses with a 403, by design, so a
 * deployed app cannot accidentally admit every registered user. Locally it
 * worked; on the server it did not. This is the method that decides.
 */
class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'name_bn', 'slug', 'email', 'password', 'role',
        'designation', 'bio', 'photo', 'facebook', 'twitter', 'youtube', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if (blank($user->slug)) {
                $user->slug = BanglaSlug::unique($user->name, static::class, $user);
            }
        });
    }

    /** Who may open the admin panel at all. Roles decide what they see inside. */
    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->is_active
            && in_array($this->role, ['admin', 'editor', 'author'], true);
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canPublish(): bool
    {
        return in_array($this->role, ['admin', 'editor'], true);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name_bn ?: $this->name;
    }
}
