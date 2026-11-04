<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ImrPrinting;
use Illuminate\Auth\Access\HandlesAuthorization;

class ImrPrintingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_issue::material');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ImrPrinting $imrPrinting): bool
    {
        return $user->can('view_issue::material');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_issue::material');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ImrPrinting $imrPrinting): bool
    {
        return $user->can('update_issue::material');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ImrPrinting $imrPrinting): bool
    {
        return $user->can('delete_issue::material');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_issue::material');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, ImrPrinting $imrPrinting): bool
    {
        return $user->can('force_delete_issue::material');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_issue::material');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, ImrPrinting $imrPrinting): bool
    {
        return $user->can('restore_issue::material');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_issue::material');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, ImrPrinting $imrPrinting): bool
    {
        return $user->can('replicate_issue::material');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_issue::material');
    }
}
