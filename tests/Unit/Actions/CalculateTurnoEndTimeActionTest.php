<?php

namespace Tests\Unit\Actions;

use App\Actions\Turnos\CalculateTurnoEndTimeAction;
use App\Models\Barberia;
use App\Models\ItemCatalogo;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculateTurnoEndTimeActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_end_time_for_a_service(): void
    {
        $barberia = Barberia::factory()->create();
        $servicio = ItemCatalogo::create([
            'barberia_id' => $barberia->id,
            'tipo' => 'servicio',
            'nombre' => 'Corte',
            'precio' => 1000,
            'duracion_minutos' => 30,
        ]);

        $inicio = Carbon::parse('2026-09-10 14:00:00');

        $action = new CalculateTurnoEndTimeAction;
        $fin = $action->execute($servicio, $inicio);

        $this->assertEquals('2026-09-10 14:30:00', $fin->format('Y-m-d H:i:s'));

        // Ensure original Carbon instance was not mutated
        $this->assertEquals('2026-09-10 14:00:00', $inicio->format('Y-m-d H:i:s'));
    }

    public function test_it_calculates_end_time_for_a_combo(): void
    {
        $barberia = Barberia::factory()->create();
        $combo = ItemCatalogo::create([
            'barberia_id' => $barberia->id,
            'tipo' => 'combo',
            'nombre' => 'Corte + Barba',
            'precio' => 2000,
            'duracion_minutos' => 45,
        ]);

        $inicio = Carbon::parse('2026-09-10 15:15:00');

        $action = new CalculateTurnoEndTimeAction;
        $fin = $action->execute($combo, $inicio);

        $this->assertEquals('2026-09-10 16:00:00', $fin->format('Y-m-d H:i:s'));
    }
}
