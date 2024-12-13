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
        if ($user->role->name === 'SUPER ADMIN') {
            return true;
        } elseif ($user->role->name === 'ADMIN') {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Noo $noo): bool
    {
        if ($user->role->name === 'SUPER ADMIN') {
            return true;
        } elseif ($user->role->name === 'ADMIN') {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->role->name === 'SUPER ADMIN') {
            return true;
        } elseif ($user->role->name === 'ADMIN') {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Noo $noo): bool
    {
        if ($user->role->name === 'SUPER ADMIN') {
            return true;
        } elseif ($user->role->name === 'ADMIN') {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Noo $noo): bool
    {
        if ($user->role->name === 'SUPER ADMIN') {
            return true;
        } elseif ($user->role->name === 'ADMIN') {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Noo $noo): bool
    {
        if ($user->role->name === 'SUPER ADMIN') {
            return true;
        } elseif ($user->role->name === 'ADMIN') {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Noo $noo): bool
    {
        if ($user->role->name === 'SUPER ADMIN') {
            return true;
        } elseif ($user->role->name === 'ADMIN') {
            return true;
        }

        return false;
    }
}
