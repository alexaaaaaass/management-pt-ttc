<?php

namespace App\Policies;

use App\Models\User;
use App\Models\OperasionalPay;
use Illuminate\Auth\Access\HandlesAuthorization;

class OperasionalPayPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_operasional::pay');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OperasionalPay $operasionalPay): bool
    {
        return $user->can('view_operasional::pay');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_operasional::pay');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OperasionalPay $operasionalPay): bool
    {
        return $user->can('update_operasional::pay');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OperasionalPay $operasionalPay): bool
    {
        return $user->can('delete_operasional::pay');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_operasional::pay');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, OperasionalPay $operasionalPay): bool
    {
        return $user->can('force_delete_operasional::pay');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_operasional::pay');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, OperasionalPay $operasionalPay): bool
    {
        return $user->can('restore_operasional::pay');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_operasional::pay');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, OperasionalPay $operasionalPay): bool
    {
        return $user->can('replicate_operasional::pay');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_operasional::pay');
    }
}
