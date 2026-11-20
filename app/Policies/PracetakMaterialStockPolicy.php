<?php

namespace App\Policies;

use App\Models\PracetakMaterialStock;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PracetakMaterialStockPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_pracetak::material::stock') || $user->hasRole('super_admin');
    }

    public function view(User $user, PracetakMaterialStock $stock): bool
    {
        return $user->can('view_pracetak::material::stock') || $user->hasRole('super_admin');
    }

    public function create(User $user): bool
    {
        // Saldo stok tidak boleh diinput manual tanpa transaksi
        return false;
    }

    public function update(User $user, PracetakMaterialStock $stock): bool
    {
        // Saldo stok tidak boleh diedit manual tanpa transaksi
        return false;
    }

    public function delete(User $user, PracetakMaterialStock $stock): bool
    {
        // Saldo stok tidak boleh dihapus manual
        return false;
    }
}
