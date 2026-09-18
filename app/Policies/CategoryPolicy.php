<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool { return $user->is_active; }
    public function view(User $user, Category $c): bool { return $user->is_active; }

    // The section list is site structure, so it stays with editors and admins.
    public function create(User $user): bool { return $user->is_active && $user->canPublish(); }
    public function update(User $user, Category $c): bool { return $user->is_active && $user->canPublish(); }
    public function delete(User $user, Category $c): bool { return $user->isAdmin(); }
    public function deleteAny(User $user): bool { return $user->isAdmin(); }
}
