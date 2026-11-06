<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CategoryItem;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryItemPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_category::item');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CategoryItem $categoryItem): bool
    {
        return $user->can('view_category::item');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_category::item');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CategoryItem $categoryItem): bool
    {
        return $user->can('update_category::item');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CategoryItem $categoryItem): bool
    {
        return $user->can('delete_category::item');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_category::item');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, CategoryItem $categoryItem): bool
    {
        return $user->can('force_delete_category::item');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_category::item');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, CategoryItem $categoryItem): bool
    {
        return $user->can('restore_category::item');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_category::item');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, CategoryItem $categoryItem): bool
    {
        return $user->can('replicate_category::item');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_category::item');
    }
}
