<?php

namespace App\Policies;

use App\Models\User;

class AppSettingPolicy
{
    /**
     * Determine whether the user can view the settings.
     */
    public function view(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update the settings.
     */
    public function update(User $user): bool
    {
        return $user->isSuperAdmin();
    }
}
