<?php

namespace Database\Seeders;

use App\Models\Barberia;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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

        $barberia = Barberia::firstOrCreate(
            ['nombre' => 'Barbería Principal'],
            [
                'horario_apertura' => '12:00',
                'horario_cierre' => '22:00',
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@barberia.com'],
            [
                'name' => 'Admin Dueño',
                'password' => Hash::make('password'),
                'barberia_id' => $barberia->id,
            ]
        );

        $role = Role::where('name', 'Dueño')->first();
        if ($role && ! $admin->hasRole('Dueño')) {
            $admin->roles()->attach($role->id);
        }

        // Crear servicios si no existen
        $serviciosData = [
            ['nombre' => 'Corte', 'precio' => 10000, 'duracion_minutos' => 30],
            ['nombre' => 'Barba', 'precio' => 8000, 'duracion_minutos' => 20],
            ['nombre' => 'Cejas', 'precio' => 5000, 'duracion_minutos' => 10],
        ];

        $serviciosModels = [];

        foreach ($serviciosData as $data) {
            $item = \App\Models\ItemCatalogo::firstOrCreate(
                [
                    'barberia_id' => $barberia->id,
                    'nombre' => $data['nombre'],
                    'tipo' => 'servicio'
                ],
                [
                    'precio' => $data['precio'],
                    'duracion_minutos' => $data['duracion_minutos']
                ]
            );

            $servicio = \App\Models\Service::firstOrCreate(
                ['item_catalogo_id' => $item->id],
                ['cuenta_para_fidelizacion' => true]
            );
            $serviciosModels[$data['nombre']] = $servicio;
        }

        // Crear combo si no existe
        $comboCorteBarbaItem = \App\Models\ItemCatalogo::firstOrCreate(
            [
                'barberia_id' => $barberia->id,
                'nombre' => 'Corte + Barba',
                'tipo' => 'combo'
            ],
            [
                'precio' => 15000,
                'duracion_minutos' => 50
            ]
        );

        $comboCorteBarba = \App\Models\Combo::firstOrCreate(
            ['item_catalogo_id' => $comboCorteBarbaItem->id]
        );

        if ($comboCorteBarba->servicios()->count() === 0) {
            $comboCorteBarba->servicios()->attach([
                $serviciosModels['Corte']->id => ['id' => \Illuminate\Support\Str::uuid()],
                $serviciosModels['Barba']->id => ['id' => \Illuminate\Support\Str::uuid()]
            ]);
        }
    }
}
