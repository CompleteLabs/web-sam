<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role->name === 'SUPER ADMIN' || $user->role->name === 'ADMIN';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->role->name === 'SUPER ADMIN' || $user->role->name === 'ADMIN';
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
    public function update(User $user, User $model): bool
    {
        return $user->role->name === 'SUPER ADMIN' || $user->role->name === 'ADMIN';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->role->name === 'SUPER ADMIN' || $user->role->name === 'ADMIN';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->role->name === 'SUPER ADMIN' || $user->role->name === 'ADMIN';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->role->name === 'SUPER ADMIN' || $user->role->name === 'ADMIN';
    }

    public function export(User $user): bool
    {
        // Check if the user has the 'SUPER ADMIN' or 'ADMIN' role
        return $user->role->name === 'SUPER ADMIN' || $user->role->name === 'ADMIN';
    }

    public function import(User $user): bool
    {
        // Check if the user has the 'SUPER ADMIN' or 'ADMIN' role
        return $user->role->name === 'SUPER ADMIN' || $user->role->name === 'ADMIN';
    }
}
