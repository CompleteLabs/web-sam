<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visit;
use Illuminate\Auth\Access\Response;

class VisitPolicy
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
    public function view(User $user, Visit $visit): bool
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
    public function update(User $user, Visit $visit): bool
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
    public function delete(User $user, Visit $visit): bool
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
    public function restore(User $user, Visit $visit): bool
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
    public function forceDelete(User $user, Visit $visit): bool
    {
        if ($user->role->name === 'SUPER ADMIN') {
            return true;
        } elseif ($user->role->name === 'ADMIN') {
            return true;
        }

        return false;
    }

    public function export(User $user): bool
    {
        // Check if the user has the 'SUPER ADMIN' or 'ADMIN' role
        return $user->role->name === 'SUPER ADMIN' || $user->role->name === 'ADMIN';
    }
}
