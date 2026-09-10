<?php

namespace Tests\Feature;

use App\Models\Barberia;
use App\Models\Client;
use App\Models\ItemCatalogo;
use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TurnoAgendaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Configuramos zona horaria como se fijó en HU-TUR-02
        config(['app.timezone' => 'America/Argentina/Buenos_Aires']);
    }

    public function test_agenda_filtra_turnos_por_fechas()
    {
        $barberia = Barberia::create([
            'nombre' => 'Test Barberia',
            'horario_apertura' => '12:00',
            'horario_cierre' => '22:00',
        ]);

        $user = User::factory()->create(['barberia_id' => $barberia->id]);
        $client = Client::create([
            'barberia_id' => $barberia->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '123456'
        ]);

        $item = ItemCatalogo::create([
            'barberia_id' => $barberia->id,
            'tipo' => 'servicio',
            'nombre' => 'Corte',
            'precio' => 5000,
            'duracion_minutos' => 30,
        ]);

        $hoy = Carbon::today();
        $mañana = Carbon::today()->addDay();

        // Turno hoy
        Turno::create([
            'barberia_id' => $barberia->id,
            'cliente_id' => $client->id,
            'usuario_id' => $user->id,
            'item_catalogo_id' => $item->id,
            'fecha_hora_inicio' => $hoy->copy()->setHour(14),
            'fecha_hora_fin' => $hoy->copy()->setHour(14)->addMinutes(30),
            'estado' => 'reservado',
        ]);

        // Turno mañana
        Turno::create([
            'barberia_id' => $barberia->id,
            'cliente_id' => $client->id,
            'usuario_id' => $user->id,
            'item_catalogo_id' => $item->id,
            'fecha_hora_inicio' => $mañana->copy()->setHour(15),
            'fecha_hora_fin' => $mañana->copy()->setHour(15)->addMinutes(30),
            'estado' => 'reservado',
        ]);

        $this->actingAs($user);

        // Sin parámetros, debería traer por defecto los de "hoy" (vista diaria por defecto)
        $response = $this->get('/turnos');
        $response->assertStatus(200);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Turnos/Index')
            ->has('turnos', 1)
        );

        // Pasando explícitamente el rango de mañana
        $response2 = $this->get('/turnos?start='.$mañana->format('Y-m-d').'&end='.$mañana->format('Y-m-d').'&view=day');
        $response2->assertStatus(200);
        $response2->assertInertia(fn (Assert $page) => $page
            ->component('Turnos/Index')
            ->has('turnos', 1)
            ->where('filters.start', $mañana->format('Y-m-d'))
        );
    }
}
