<?php

namespace App\Policies;

use App\Models\Division;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class DivisionPolicy
{
    public function viewAny(User $user): bool
    {
        return Gate::allows('view_any_division');
    }

    public function view(User $user, Division $division): bool
    {
        return Gate::allows('view_division');
    }

    public function create(User $user): bool
    {
        return Gate::allows('create_division');
    }

    public function update(User $user, Division $division): bool
    {
        return Gate::allows('update_division');
    }

    public function deleteAny(User $user): bool
    {
        return Gate::allows('delete_any_division');
    }

    public function delete(User $user, Division $division): bool
    {
        return Gate::allows('delete_division');
    }
}
