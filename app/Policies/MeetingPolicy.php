<?php

namespace App\Policies;

use App\Models\Meeting;
use App\Models\User;

class MeetingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Meeting $meeting): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Meeting $meeting): bool
    {
        return true;
    }

    /**
     * No se permite borrar una reunion que ya tiene compromisos
     * vinculados, para no dejar huerfano su "origen" (regla de negocio,
     * no solo un problema de integridad referencial en BD).
     */
    public function delete(User $user, Meeting $meeting): bool
    {
        return ! $meeting->commitments()->exists();
    }

    public function restore(User $user, Meeting $meeting): bool
    {
        return true;
    }

    public function forceDelete(User $user, Meeting $meeting): bool
    {
        return false;
    }
}
