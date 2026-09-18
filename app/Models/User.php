<?php

namespace App\Models;

use App\Support\BanglaSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
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
