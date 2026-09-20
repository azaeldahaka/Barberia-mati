<?php

namespace Tests\Feature;

use App\Models\Barberia;
use App\Models\Client;
use App\Models\ItemCatalogo;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_empty_state_when_no_turnos(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('cantidadTurnosHoy', 0)
            ->where('ingresosEstimadosHoy', 0)
            ->where('servicioMasSolicitado', null)
        );
    }

    public function test_dashboard_calculates_daily_metrics_correctly(): void
    {
        $user = User::factory()->create();
        $barberia = Barberia::factory()->create();
        $client = new Client(['first_name' => 'John', 'last_name' => 'Doe', 'phone' => '123456']);
        $client->save();
        
        $corte = new ItemCatalogo(['nombre' => 'Corte', 'precio' => 5000, 'tipo' => 'servicio', 'duracion_minutos' => 30]);
        $corte->barberia_id = $barberia->id;
        $corte->save();

        $barba = new ItemCatalogo(['nombre' => 'Barba', 'precio' => 3000, 'tipo' => 'servicio', 'duracion_minutos' => 20]);
        $barba->barberia_id = $barberia->id;
        $barba->save();

        $hoy = today();

        // Turno 1: Hoy, completado (Corte) -> Suma cantidad (1), ingreso (5000)
        Turno::create([
            'barberia_id' => $barberia->id,
            'cliente_id' => $client->id,
            'usuario_id' => $user->id,
            'item_catalogo_id' => $corte->id,
            'fecha_hora_inicio' => $hoy->copy()->addHours(13),
            'fecha_hora_fin' => $hoy->copy()->addHours(14),
            'estado' => 'completado',
        ]);

        // Turno 2: Hoy, reservado (Corte) -> Suma cantidad (2), ingreso (+5000 = 10000)
        Turno::create([
            'barberia_id' => $barberia->id,
            'cliente_id' => $client->id,
            'usuario_id' => $user->id,
            'item_catalogo_id' => $corte->id,
            'fecha_hora_inicio' => $hoy->copy()->addHours(15),
            'fecha_hora_fin' => $hoy->copy()->addHours(16),
            'estado' => 'reservado',
        ]);

        // Turno 3: Hoy, ausente (Barba) -> Suma cantidad (3), NO suma ingreso
        Turno::create([
            'barberia_id' => $barberia->id,
            'cliente_id' => $client->id,
            'usuario_id' => $user->id,
            'item_catalogo_id' => $barba->id,
            'fecha_hora_inicio' => $hoy->copy()->addHours(17),
            'fecha_hora_fin' => $hoy->copy()->addHours(18),
            'estado' => 'ausente',
        ]);

        // Turno 4: Hoy, cancelado (Corte) -> NO suma cantidad, NO suma ingreso
        Turno::create([
            'barberia_id' => $barberia->id,
            'cliente_id' => $client->id,
            'usuario_id' => $user->id,
            'item_catalogo_id' => $corte->id,
            'fecha_hora_inicio' => $hoy->copy()->addHours(19),
            'fecha_hora_fin' => $hoy->copy()->addHours(20),
            'estado' => 'cancelado',
        ]);

        // Turno 5: Ayer, completado (Corte) -> NO suma porque no es hoy
        Turno::create([
            'barberia_id' => $barberia->id,
            'cliente_id' => $client->id,
            'usuario_id' => $user->id,
            'item_catalogo_id' => $corte->id,
            'fecha_hora_inicio' => $hoy->copy()->subDay()->addHours(13),
            'fecha_hora_fin' => $hoy->copy()->subDay()->addHours(14),
            'estado' => 'completado',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('cantidadTurnosHoy', 3)
            ->where('ingresosEstimadosHoy', 10000)
            ->where('servicioMasSolicitado', 'Corte')
        );
    }
}
