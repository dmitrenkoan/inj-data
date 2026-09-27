<?php

namespace App\Policies;

use App\Models\Unit;
use App\Models\User;

class UnitPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Unit $unit): bool
    {
        return $this->canAccess($user, $unit);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Unit $unit): bool
    {
        return $this->canAccess($user, $unit);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Unit $unit): bool
    {
        return $this->canAccess($user, $unit);
    }

    private function canAccess(User $user, Unit $unit): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isBrigade()) {
            return $unit->battalion->brigade_id === $user->brigade_id;
        }

        return $unit->battalion_id === $user->battalion_id;
    }
}
