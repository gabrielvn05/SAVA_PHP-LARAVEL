<?php

namespace App\Policies;

use App\Enums\CapabilityType;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasCapability(CapabilityType::GestionarUsuarios);
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasCapability(CapabilityType::GestionarUsuarios);
    }
}
