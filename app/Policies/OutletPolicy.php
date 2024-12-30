<?php

namespace App\Policies;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OutletPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role->name === 'SUPER ADMIN' || $user->role->name === 'ADMIN' || $user->role->name === 'AR' || $user->role->name === 'FINANCE';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Outlet $outlet): bool
    {
        return $user->role->name === 'SUPER ADMIN' || $user->role->name === 'ADMIN' || $user->role->name === 'AR' ||$user->role->name === 'FINANCE';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role->name === 'SUPER ADMIN' || $user->role->name === 'ADMIN';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Outlet $outlet): bool
    {
        return $user->role->name === 'SUPER ADMIN' || $user->role->name === 'ADMIN';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Outlet $outlet): bool
    {
        return $user->role->name === 'SUPER ADMIN' || $user->role->name === 'ADMIN';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Outlet $outlet): bool
    {
        return $user->role->name === 'SUPER ADMIN' || $user->role->name === 'ADMIN';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Outlet $outlet): bool
    {
        return $user->role->name === 'SUPER ADMIN' || $user->role->name === 'ADMIN';
    }

    public function exportAll(User $user): bool
    {
        return $user->role->name === 'SUPER ADMIN' || $user->role->name === 'ADMIN' || $user->role->name === 'AR' || $user->role->name === 'FINANCE';
    }
}
