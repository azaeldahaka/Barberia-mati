<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
        ]);

        $barberia = \App\Models\Barberia::firstOrCreate(['nombre' => 'Barbería Principal']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@barberia.com'],
            [
                'name' => 'Admin Dueño',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'barberia_id' => $barberia->id,
            ]
        );

        $role = \App\Models\Role::where('name', 'Dueño')->first();
        if ($role && !$admin->hasRole('Dueño')) {
            $admin->roles()->attach($role->id);
        }
    }
}
