<?php

namespace App\Policies;

use App\Shared\Models\User;
use App\Shared\Models\GuestList;
use Illuminate\Auth\Access\HandlesAuthorization;

class GuestListPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GuestList $guestList): bool
    {
        return $user->id === $guestList->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GuestList $guestList): bool
    {
        return $user->id === $guestList->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GuestList $guestList): bool
    {
        return $user->id === $guestList->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, GuestList $guestList): bool
    {
        return $user->id === $guestList->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, GuestList $guestList): bool
    {
        return $user->id === $guestList->user_id;
    }
}
