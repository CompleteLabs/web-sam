<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

class UserPolicy
{
    public function restoreAny(User $user): bool
    {
        return Gate::allows('restore_any_user');
    }

    public function deleteAny(User $user): bool
    {
        return Gate::allows('delete_any_user');
    }

    public function forceDeleteAny(User $user): bool
    {
        return Gate::allows('force_delete_any_user');
    }

    public function viewAny(User $user): bool
    {
        return Gate::allows('view_any_user');
    }

    public function view(User $user, User $model): bool
    {
        return Gate::allows('view_user');
    }

    public function create(User $user): bool
    {
        return Gate::allows('create_user');
    }

    public function update(User $user, User $model): bool
    {
        return Gate::allows('update_user');
    }

    public function delete(User $user, User $model): bool
    {
        return Gate::allows('delete_user');
    }

    public function restore(User $user, User $model): bool
    {
        return Gate::allows('restore_user');
    }

    public function forceDelete(User $user, User $model): bool
    {
        return Gate::allows('force_delete_user');
    }

    public function export(User $user): bool
    {
        return Gate::allows('export_user');
    }

    public function import(User $user): bool
    {
        return Gate::allows('import_user');
    }
}
