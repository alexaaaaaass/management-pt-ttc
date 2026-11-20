<?php

namespace App\Policies;

use App\Models\PracetakMaterialMovement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PracetakMaterialMovementPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_pracetak::material::movement') || $user->hasRole('super_admin');
    }

    public function view(User $user, PracetakMaterialMovement $movement): bool
    {
        return $user->can('view_pracetak::material::movement') || $user->hasRole('super_admin');
    }

    public function create(User $user): bool
    {
        // Mutasi ledger tidak boleh dibuat manual di luar transaksi
        return false;
    }

    public function update(User $user, PracetakMaterialMovement $movement): bool
    {
        // Ledger mutasi bersifat immutable
        return false;
    }

    public function delete(User $user, PracetakMaterialMovement $movement): bool
    {
        // Ledger mutasi bersifat immutable
        return false;
    }
}
