<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MasterMaterialPlate;
use Illuminate\Auth\Access\HandlesAuthorization;

class MasterMaterialPlatePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_master::material::plate');
    }

    public function view(User $user, MasterMaterialPlate $masterMaterialPlate): bool
    {
        return $user->can('view_master::material::plate');
    }

    public function create(User $user): bool
    {
        return $user->can('create_master::material::plate');
    }

    public function update(User $user, MasterMaterialPlate $masterMaterialPlate): bool
    {
        return $user->can('update_master::material::plate');
    }

    public function delete(User $user, MasterMaterialPlate $masterMaterialPlate): bool
    {
        return $user->can('delete_master::material::plate');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_master::material::plate');
    }

    public function forceDelete(User $user, MasterMaterialPlate $masterMaterialPlate): bool
    {
        return $user->can('force_delete_master::material::plate');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_master::material::plate');
    }

    public function restore(User $user, MasterMaterialPlate $masterMaterialPlate): bool
    {
        return $user->can('restore_master::material::plate');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_master::material::plate');
    }

    public function replicate(User $user, MasterMaterialPlate $masterMaterialPlate): bool
    {
        return $user->can('replicate_master::material::plate');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_master::material::plate');
    }
}
