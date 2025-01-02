<?php

namespace App\Policies;

use App\Models\PlanVisit;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class PlanVisitPolicy
{
    public function viewAny(User $user): bool
    {
        return Gate::allows('view_any_plan::visit');
    }

    public function view(User $user, PlanVisit $planVisit): bool
    {
        return Gate::allows('view_plan::visit');
    }

    public function create(User $user): bool
    {
        return Gate::allows('create_plan::visit');
    }

    public function update(User $user, PlanVisit $planVisit): bool
    {
        return Gate::allows('update_plan::visit');
    }

    public function delete(User $user, PlanVisit $planVisit): bool
    {
        return Gate::allows('delete_plan::visit');
    }

    public function deleteAny(User $user): bool
    {
        return Gate::allows('delete_any_plan::visit');
    }

    public function export(User $user): bool
    {
        return Gate::allows('export_plan::visit');
    }
}
