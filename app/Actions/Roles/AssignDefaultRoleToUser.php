<?php

namespace App\Actions\Roles;

use App\Models\Role;
use App\Models\User;
use App\Models\Barberia;

class AssignDefaultRoleToUser
{
    /**
     * Asigna el rol por defecto (Dueño) y el tenant por defecto a un usuario.
     */
    public function handle(User $user): void
    {
        $barberia = Barberia::firstOrCreate(['nombre' => 'Barbería Principal']);
        
        if (! $user->barberia_id) {
            $user->barberia_id = $barberia->id;
            $user->save();
        }

        $role = Role::firstOrCreate(['name' => 'Cliente']);

        if (! $user->hasRole('Cliente')) {
            $user->roles()->syncWithoutDetaching([$role->id]);
        }
    }
}
