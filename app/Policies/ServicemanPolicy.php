<?php

namespace App\Policies;

use App\Models\Serviceman;
use App\Models\User;

class ServicemanPolicy
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
    public function view(User $user, Serviceman $serviceman): bool
    {
        return $this->canAccess($user, $serviceman);
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
    public function update(User $user, Serviceman $serviceman): bool
    {
        return $this->canAccess($user, $serviceman);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Serviceman $serviceman): bool
    {
        return $this->canAccess($user, $serviceman);
    }

    private function canAccess(User $user, Serviceman $serviceman): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isBrigade()) {
            return $serviceman->unit->battalion->brigade_id === $user->brigade_id;
        }

        return $serviceman->unit->battalion_id === $user->battalion_id;
    }
}
