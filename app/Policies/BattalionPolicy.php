<?php

namespace App\Policies;

use App\Models\Battalion;
use App\Models\User;

class BattalionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isBrigade();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Battalion $battalion): bool
    {
        return $this->canAccess($user, $battalion);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isBrigade();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Battalion $battalion): bool
    {
        return $this->canAccess($user, $battalion);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Battalion $battalion): bool
    {
        return $this->canAccess($user, $battalion);
    }

    private function canAccess(User $user, Battalion $battalion): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isBrigade() && $battalion->brigade_id === $user->brigade_id;
    }
}
