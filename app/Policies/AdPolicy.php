<?php

namespace App\Policies;

use App\Models\Ad;
use App\Models\User;

class AdPolicy
{
    // Advertising is money, so it is admin-only.
    public function viewAny(User $user): bool { return $user->isAdmin(); }
    public function view(User $user, Ad $ad): bool { return $user->isAdmin(); }
    public function create(User $user): bool { return $user->isAdmin(); }
    public function update(User $user, Ad $ad): bool { return $user->isAdmin(); }
    public function delete(User $user, Ad $ad): bool { return $user->isAdmin(); }
    public function deleteAny(User $user): bool { return $user->isAdmin(); }
}
