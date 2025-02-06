<?php

namespace App\Policies;

use App\Models\CustomAttribute;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class CustomAttributePolicy
{
    public function viewAny(User $user): bool
    {
        return Gate::allows('view_any_custom::attribute');
    }

    public function view(User $user, CustomAttribute $customAttribute): bool
    {
        return Gate::allows('view_custom::attribute');
    }

    public function create(User $user): bool
    {
        return Gate::allows('create_custom::attribute');
    }

    public function update(User $user, CustomAttribute $customAttribute): bool
    {
        return Gate::allows('update_custom::attribute');
    }

    public function deleteAny(User $user): bool
    {
        return Gate::allows('delete_any_custom::attribute');
    }

    public function delete(User $user, CustomAttribute $customAttribute): bool
    {
        return Gate::allows('delete_custom::attribute');
    }
}
