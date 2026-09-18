<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;

class TagPolicy
{
    public function viewAny(User $user): bool { return $user->is_active; }
    public function view(User $user, Tag $t): bool { return $user->is_active; }
    // Authors add tags while writing, so tag creation stays open.
    public function create(User $user): bool { return $user->is_active; }
    public function update(User $user, Tag $t): bool { return $user->is_active && $user->canPublish(); }
    public function delete(User $user, Tag $t): bool { return $user->is_active && $user->canPublish(); }
    public function deleteAny(User $user): bool { return $user->is_active && $user->canPublish(); }
}
