<?php

namespace App\Policies;

use App\Models\Region;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class RegionPolicy
{
    public function viewAny(User $user): bool
    {
        return Gate::allows('view_any_region');
    }

    public function view(User $user, Region $region): bool
    {
        return Gate::allows('view_region');
    }

    public function create(User $user): bool
    {
        return Gate::allows('create_region');
    }

    public function update(User $user, Region $region): bool
    {
        return Gate::allows('update_region');
    }

    public function deleteAny(User $user): bool
    {
        return Gate::allows('delete_any_region');
    }

    public function delete(User $user, Region $region): bool
    {
        return Gate::allows('delete_region');
    }
}
