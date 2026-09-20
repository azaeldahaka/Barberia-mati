<?php

namespace Tests\Feature;

use App\Actions\Turnos\CheckTurnoOverlapAction;
use App\Models\Barberia;
use App\Models\Client;
use App\Models\ItemCatalogo;
use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TurnoOverlapExcludeTest extends TestCase
{
    use RefreshDatabase;

    public function test_exclude_turno_from_overlap_check()
    {
        $barberia = Barberia::factory()->create();
        $user = User::factory()->create(['barberia_id' => $barberia->id]);
        $client = Client::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '1234567890',
        ]);
        $item = ItemCatalogo::create([
            'barberia_id' => $barberia->id,
            'nombre' => 'Corte',
            'tipo' => 'servicio',
            'precio' => 2000,
            'duracion_minutos' => 30,
        ]);

        $turno = Turno::create([
            'barberia_id' => $barberia->id,
            'cliente_id' => $client->id,
            'usuario_id' => $user->id,
            'item_catalogo_id' => $item->id,
            'fecha_hora_inicio' => Carbon::now()->addDays(1)->setHour(14)->setMinute(0),
            'fecha_hora_fin' => Carbon::now()->addDays(1)->setHour(14)->setMinute(30),
            'estado' => 'reservado',
        ]);

        $action = new CheckTurnoOverlapAction;

        // 1. Should overlap if not excluded
        $overlaps = $action->execute(
            $barberia->id,
            Carbon::now()->addDays(1)->setHour(14)->setMinute(0),
            Carbon::now()->addDays(1)->setHour(14)->setMinute(30)
        );
        $this->assertTrue($overlaps, 'It should overlap when not excluded');

        // 2. Should NOT overlap if excluded
        $overlapsExcluded = $action->execute(
            $barberia->id,
            Carbon::now()->addDays(1)->setHour(14)->setMinute(0),
            Carbon::now()->addDays(1)->setHour(14)->setMinute(30),
            $turno->id
        );
        $this->assertFalse($overlapsExcluded, 'It should NOT overlap when excluded');
    }
}
