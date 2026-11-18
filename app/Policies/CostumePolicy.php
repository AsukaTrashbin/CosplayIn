<?php

namespace App\Policies;

use App\Models\Costume;
use App\Models\User;

class CostumePolicy
{
    public function view(?User $user, Costume $costume): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isTenant() && $user->status === 'active';
    }

    public function update(User $user, Costume $costume): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isTenant()
            && $user->tenant
            && $costume->tenant_id === $user->tenant->id;
    }

    public function delete(User $user, Costume $costume): bool
    {
        return $this->update($user, $costume);
    }
}
