<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Facades\Gate;

class VisitPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return Gate::allows('view_any_visit');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Visit $visit): bool
    {
        return Gate::allows('view_visit');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return Gate::allows('create_visit');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Visit $visit): bool
    {
        return Gate::allows('update_visit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Visit $visit): bool
    {
        return Gate::allows('delete_visit');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Visit $visit): bool
    {
       return Gate::allows('view_any_visit');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Visit $visit): bool
    {
       return Gate::allows('force_delete_visit');
    }

    public function export(User $user): bool
    {
        return Gate::allows('export_visit');
    }
}
