<?php

namespace App\Policies;

use App\Models\Commitment;
use App\Models\User;

class CommitmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Commitment $commitment): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Commitment $commitment): bool
    {
        return true;
    }

    public function delete(User $user, Commitment $commitment): bool
    {
        return true;
    }

    public function restore(User $user, Commitment $commitment): bool
    {
        return true;
    }

    public function forceDelete(User $user, Commitment $commitment): bool
    {
        return false;
    }
}
