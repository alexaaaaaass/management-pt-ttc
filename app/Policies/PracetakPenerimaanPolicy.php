<?php

namespace App\Policies;

use App\Models\PracetakPenerimaan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PracetakPenerimaanPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_pracetak::penerimaan') || $user->hasRole('super_admin');
    }

    public function view(User $user, PracetakPenerimaan $penerimaan): bool
    {
        return $user->can('view_pracetak::penerimaan') || $user->hasRole('super_admin');
    }

    public function create(User $user): bool
    {
        return $user->can('create_pracetak::penerimaan') || $user->hasRole('super_admin');
    }

    public function update(User $user, PracetakPenerimaan $penerimaan): bool
    {
        // Transaksi yang sudah posted tidak dapat diubah
        if ($penerimaan->isPosted()) {
            return false;
        }

        return $user->can('update_pracetak::penerimaan') || $user->hasRole('super_admin');
    }

    public function delete(User $user, PracetakPenerimaan $penerimaan): bool
    {
        // Transaksi yang sudah posted tidak dapat dihapus
        if ($penerimaan->isPosted()) {
            return false;
        }

        return $user->can('delete_pracetak::penerimaan') || $user->hasRole('super_admin');
    }

    public function post(User $user, PracetakPenerimaan $penerimaan): bool
    {
        if ($penerimaan->isPosted()) {
            return false;
        }

        return $user->can('post_pracetak::penerimaan') 
            || $user->can('update_pracetak::penerimaan') 
            || $user->hasRole('super_admin');
    }
}
