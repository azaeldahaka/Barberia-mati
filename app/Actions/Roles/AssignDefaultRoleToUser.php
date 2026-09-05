<?php

namespace App\Actions\Roles;

use App\Models\Role;
use App\Models\User;

class AssignDefaultRoleToUser
{
    /**
     * Asigna el rol por defecto (Dueño) a un usuario.
     */
    public function handle(User $user): void
    {
        $role = Role::firstOrCreate(['name' => 'Dueño']);

        if (! $user->hasRole('Dueño')) {
            $user->roles()->syncWithoutDetaching([$role->id]);
        }
    }
}
