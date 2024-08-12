<?php

namespace App\Policies;

use App\Models\User;
use App\Models\IzinLemburApproveDua;
use Illuminate\Auth\Access\HandlesAuthorization;

class IzinLemburApproveDuaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_izin::lembur::approve::dua');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, IzinLemburApproveDua $izinLemburApproveDua): bool
    {
        return $user->can('view_izin::lembur::approve::dua');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_izin::lembur::approve::dua');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, IzinLemburApproveDua $izinLemburApproveDua): bool
    {
        return $user->can('update_izin::lembur::approve::dua');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, IzinLemburApproveDua $izinLemburApproveDua): bool
    {
        return $user->can('delete_izin::lembur::approve::dua');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_izin::lembur::approve::dua');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, IzinLemburApproveDua $izinLemburApproveDua): bool
    {
        return $user->can('force_delete_izin::lembur::approve::dua');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_izin::lembur::approve::dua');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, IzinLemburApproveDua $izinLemburApproveDua): bool
    {
        return $user->can('restore_izin::lembur::approve::dua');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_izin::lembur::approve::dua');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, IzinLemburApproveDua $izinLemburApproveDua): bool
    {
        return $user->can('replicate_izin::lembur::approve::dua');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_izin::lembur::approve::dua');
    }
}
