<?php

namespace App\Services;

use App\Models\User;

class CapabilitySeeder
{
    public function seedForUser(User $user): void
    {
        $user->capabilities()->delete();

        foreach ($user->rol->defaultCapabilities() as $capability) {
            $user->capabilities()->create([
                'capability' => $capability,
            ]);
        }
    }
}
