<?php

namespace App\Policies;

use App\Models\Noo;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NooPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role->name === 'SUPER ADMIN' || $user->role->name === 'ADMIN' || $user->role->name === 'AR';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Noo $noo): bool
    {
        return $user->role->name === 'SUPER ADMIN' || $user->role->name === 'ADMIN' || $user->role->name === 'AR';
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
    public function update(User $user, Noo $noo): bool
    {
        return $user->role->name === 'SUPER ADMIN';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Noo $noo): bool
    {
        return $user->role->name === 'SUPER ADMIN';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Noo $noo): bool
    {
        return $user->role->name === 'SUPER ADMIN';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Noo $noo): bool
    {
        return $user->role->name === 'SUPER ADMIN';
    }

    public function export(User $user): bool
    {
        // Check if the user has the 'SUPER ADMIN' or 'ADMIN' role
        return $user->role->name === 'SUPER ADMIN' || $user->role->name === 'ADMIN' || $user->role->name === 'AR';
    }

    public function approve(User $user, Noo $noo)
    {
        // Logika untuk memeriksa apakah user bisa melakukan approve
        return $user->role->name === 'AR';
    }

    // Method untuk memeriksa apakah user bisa reject
    public function reject(User $user, Noo $noo)
    {
        // Logika untuk memeriksa apakah user bisa melakukan reject
        return $user->role->name === 'AR';
    }
}
