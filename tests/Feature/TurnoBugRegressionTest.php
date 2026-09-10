<?php

namespace Tests\Feature;

use App\Models\Barberia;
use App\Models\Client;
use App\Models\ItemCatalogo;
use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TurnoBugRegressionTest extends TestCase
{
    use RefreshDatabase;

    private Barberia $barberia;

    private User $user;

    private ItemCatalogo $servicio40m;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->barberia = Barberia::create([
            'nombre' => 'Barbería Test',
            'horario_apertura' => '12:00',
            'horario_cierre' => '22:00',
        ]);

        $this->user = User::factory()->create([
            'barberia_id' => $this->barberia->id,
        ]);

        $this->servicio40m = ItemCatalogo::create([
            'barberia_id' => $this->barberia->id,
            'tipo' => 'servicio',
            'nombre' => 'Corte 40 min',
            'precio' => 5000,
            'duracion_minutos' => 40,
        ]);

        $this->client = Client::create([
            'barberia_id' => $this->barberia->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '1234567890',
        ]);
    }

    /**
     * Bug 1 Regression: Grid should respect service duration and 5 min buffer.
     * Service of 40m at 17:00 -> 17:30 must be rejected.
     */
    public function test_bug1_turnos_overlap_buffer_validation()
    {
        $fechaTurnoA = Carbon::tomorrow()->setTime(17, 0);

        // Crear turno existente de 17:00 a 17:40
        Turno::create([
            'barberia_id' => $this->barberia->id,
            'cliente_id' => $this->client->id,
            'usuario_id' => $this->user->id,
            'item_catalogo_id' => $this->servicio40m->id,
            'fecha_hora_inicio' => $fechaTurnoA,
            'fecha_hora_fin' => $fechaTurnoA->copy()->addMinutes(40),
            'estado' => 'reservado',
        ]);

        // Intentar agendar a las 17:30 -> debe fallar porque se solapa
        $response1730 = $this->withSession(['client_id' => $this->client->id])
            ->post('/reservar', [
                'item_catalogo_id' => $this->servicio40m->id,
                'fecha_hora_inicio' => $fechaTurnoA->copy()->setTime(17, 30)->format('Y-m-d\TH:i'),
            ]);
        $response1730->assertSessionHasErrors('fecha_hora_inicio');

        // Intentar agendar a las 17:40 -> debe fallar por el buffer de 5 minutos
        $response1740 = $this->withSession(['client_id' => $this->client->id])
            ->post('/reservar', [
                'item_catalogo_id' => $this->servicio40m->id,
                'fecha_hora_inicio' => $fechaTurnoA->copy()->setTime(17, 40)->format('Y-m-d\TH:i'),
            ]);
        $response1740->assertSessionHasErrors('fecha_hora_inicio');

        // Intentar agendar a las 17:45 -> debe pasar
        $response1745 = $this->withSession(['client_id' => $this->client->id])
            ->post('/reservar', [
                'item_catalogo_id' => $this->servicio40m->id,
                'fecha_hora_inicio' => $fechaTurnoA->copy()->setTime(17, 45)->format('Y-m-d\TH:i'),
            ]);
        $response1745->assertSessionHasNoErrors();
    }

    /**
     * Bug 2 Regression: Redirect on success and availability updates.
     */
    public function test_bug2_redirects_and_updates_availability()
    {
        $fechaTurno = Carbon::tomorrow()->setTime(14, 0);

        // Check availability page BEFORE booking (Inertia check)
        $responseBefore = $this->get('/reservar?date='.$fechaTurno->format('Y-m-d'));
        // Inertia prop turnosDelDia should be empty
        $responseBefore->assertInertia(fn ($page) => $page->component('Turnos/PublicCreate')
            ->has('turnosDelDia', 0));

        // Submit form
        $response = $this->withSession(['client_id' => $this->client->id])
            ->post('/reservar', [
                'item_catalogo_id' => $this->servicio40m->id,
                'fecha_hora_inicio' => $fechaTurno->format('Y-m-d\TH:i'),
            ]);

        // Must redirect to home page, NOT back to /reservar
        $response->assertRedirect('/');

        // Check availability page AFTER booking
        $responseAfter = $this->get('/reservar?date='.$fechaTurno->format('Y-m-d'));
        $responseAfter->assertInertia(fn ($page) => $page->component('Turnos/PublicCreate')
            ->has('turnosDelDia', 1));
    }

    /**
     * Bug 3 Regression: Timezone consistency check.
     */
    public function test_bug3_timezone_consistency()
    {
        // 1. Verify app timezone is correct
        $this->assertEquals('America/Argentina/Buenos_Aires', config('app.timezone'));

        // 2. Submit booking at exactly 21:00
        $fechaTurno = Carbon::tomorrow()->setTime(21, 0);

        $this->withSession(['client_id' => $this->client->id])
            ->post('/reservar', [
                'item_catalogo_id' => $this->servicio40m->id,
                'fecha_hora_inicio' => $fechaTurno->format('Y-m-d\TH:i'),
            ]);

        // 3. Verify DB has EXACTLY 21:00 (since it uses ART and stores it as such)
        $turno = Turno::first();
        $this->assertNotNull($turno);
        $this->assertEquals('21:00:00', Carbon::parse($turno->fecha_hora_inicio)->format('H:i:s'));

        // 4. Verify that when reading via Staff Dashboard (JSON serialization), it remains 21:00
        $response = $this->actingAs($this->user)->get('/turnos');
        $response->assertInertia(fn ($page) => $page->component('Turnos/Index')
            ->where('turnos.0.fecha_hora_inicio', $fechaTurno->format('Y-m-d H:i:s'))
        );
    }
}
