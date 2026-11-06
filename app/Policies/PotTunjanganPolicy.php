<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PotTunjangan;
use Illuminate\Auth\Access\HandlesAuthorization;

class PotTunjanganPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_pot::tunjangan');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PotTunjangan $potTunjangan): bool
    {
        return $user->can('view_pot::tunjangan');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_pot::tunjangan');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PotTunjangan $potTunjangan): bool
    {
        return $user->can('update_pot::tunjangan');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PotTunjangan $potTunjangan): bool
    {
        return $user->can('delete_pot::tunjangan');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_pot::tunjangan');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, PotTunjangan $potTunjangan): bool
    {
        return $user->can('force_delete_pot::tunjangan');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_pot::tunjangan');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, PotTunjangan $potTunjangan): bool
    {
        return $user->can('restore_pot::tunjangan');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_pot::tunjangan');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, PotTunjangan $potTunjangan): bool
    {
        return $user->can('replicate_pot::tunjangan');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_pot::tunjangan');
    }
}
