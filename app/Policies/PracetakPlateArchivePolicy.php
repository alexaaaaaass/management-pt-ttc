<?php

namespace App\Policies;

use App\Models\PracetakPlateArchive;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PracetakPlateArchivePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_pracetak::plate::archive') || $user->hasRole('super_admin');
    }

    public function view(User $user, PracetakPlateArchive $archive): bool
    {
        return $user->can('view_pracetak::plate::archive') || $user->hasRole('super_admin');
    }

    public function create(User $user): bool
    {
        return $user->can('create_pracetak::plate::archive') || $user->hasRole('super_admin');
    }

    public function update(User $user, PracetakPlateArchive $archive): bool
    {
        return $user->can('update_pracetak::plate::archive') || $user->hasRole('super_admin');
    }

    public function delete(User $user, PracetakPlateArchive $archive): bool
    {
        return $user->can('delete_pracetak::plate::archive') || $user->hasRole('super_admin');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_pracetak::plate::archive') || $user->hasRole('super_admin');
    }
}
