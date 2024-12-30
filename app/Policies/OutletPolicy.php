<?php

namespace App\Policies;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class OutletPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return Gate::allows('view_any_outlet');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Outlet $outlet): bool
    {
        return Gate::allows('view_outlet');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return Gate::allows('create_outlet');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Outlet $outlet): bool
    {
        return Gate::allows('update_outlet');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Outlet $outlet): bool
    {
        return Gate::allows('delete_outlet');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Outlet $outlet): bool
    {
        return Gate::allows('restore_outlet');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Outlet $outlet): bool
    {
        return Gate::allows('force_delete_outlet');
    }

    public function exportAll(User $user): bool
    {
        return Gate::allows('export_outlet');
    }
}
