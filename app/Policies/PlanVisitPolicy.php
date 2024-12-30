<?php

namespace App\Policies;

use App\Models\PlanVisit;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class PlanVisitPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return Gate::allows('view_any_plan::visit');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PlanVisit $planVisit): bool
    {
        return Gate::allows('view_plan::visit');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return Gate::allows('create_plan::visit');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PlanVisit $planVisit): bool
    {
        return Gate::allows('update_plan::visit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PlanVisit $planVisit): bool
    {
        return Gate::allows('delete_plan::visit');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PlanVisit $planVisit): bool
    {
        return Gate::allows('restore_plan::visit');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PlanVisit $planVisit): bool
    {
        return Gate::allows('force_delete_plan::visit');
    }

    public function export(User $user): bool
    {
        // Check if the user has the 'SUPER ADMIN' or 'ADMIN' role
        return Gate::allows('export_plan::visit');
    }
}
