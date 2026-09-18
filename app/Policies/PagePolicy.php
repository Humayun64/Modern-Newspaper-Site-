<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;

class PagePolicy
{
    public function viewAny(User $user): bool { return $user->is_active && $user->canPublish(); }
    public function view(User $user, Page $p): bool { return $user->is_active && $user->canPublish(); }
    public function create(User $user): bool { return $user->is_active && $user->canPublish(); }
    public function update(User $user, Page $p): bool { return $user->is_active && $user->canPublish(); }
    public function delete(User $user, Page $p): bool { return $user->isAdmin(); }
    public function deleteAny(User $user): bool { return $user->isAdmin(); }
}
