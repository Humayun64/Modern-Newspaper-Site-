<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Post $post): bool
    {
        return $user->is_active && ($user->canPublish() || $post->author_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /** Authors may edit their own work; editors and admins may edit anyone's. */
    public function update(User $user, Post $post): bool
    {
        return $user->is_active && ($user->canPublish() || $post->author_id === $user->id);
    }

    /** Deleting is an editor decision, not an author one. */
    public function delete(User $user, Post $post): bool
    {
        return $user->is_active && $user->canPublish();
    }

    public function deleteAny(User $user): bool
    {
        return $user->is_active && $user->canPublish();
    }

    public function restore(User $user, Post $post): bool
    {
        return $user->is_active && $user->canPublish();
    }

    public function forceDelete(User $user, Post $post): bool
    {
        return $user->isAdmin();
    }
}
