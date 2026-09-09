<?php

namespace Database\Factories;

use App\Models\Barberia;
use Illuminate\Database\Eloquent\Factories\Factory;

class BarberiaFactory extends Factory
{
    protected $model = Barberia::class;

    public function definition(): array
    {
        return [
            'nombre' => 'Barberia de Matias',
            'horario_apertura' => '12:00',
            'horario_cierre' => '22:00',
        ];
    }
}
