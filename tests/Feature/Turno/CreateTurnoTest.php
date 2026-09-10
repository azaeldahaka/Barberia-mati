<?php

namespace Tests\Feature\Turno;

use App\Models\Barberia;
use App\Models\Client;
use App\Models\ItemCatalogo;
use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTurnoTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Barberia $barberia;

    private ItemCatalogo $itemCatalogo;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->barberia = Barberia::factory()->create([
            'horario_apertura' => '12:00',
            'horario_cierre' => '22:00',
        ]);

        $this->user = User::factory()->create([
            'barberia_id' => $this->barberia->id,
        ]);

        $this->itemCatalogo = ItemCatalogo::create([
            'barberia_id' => $this->barberia->id,
            'tipo' => 'servicio',
            'nombre' => 'Corte',
            'precio' => 5000,
            'duracion_minutos' => 30,
        ]);

        $this->client = Client::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '1234567890',
        ]);
    }

    public function test_staff_can_create_turno_with_existing_client()
    {
        $fechaInicio = Carbon::today()->setTime(14, 0);

        $response = $this->actingAs($this->user)->post(route('turnos.store'), [
            'client_id' => $this->client->id,
            'item_catalogo_id' => $this->itemCatalogo->id,
            'fecha_hora_inicio' => $fechaInicio->format('Y-m-d\TH:i'),
        ]);

        $response->assertRedirect(route('turnos.index'));
        $response->assertSessionHas('status', 'Turno agendado exitosamente.');

        $this->assertDatabaseHas('turnos', [
            'barberia_id' => $this->barberia->id,
            'cliente_id' => $this->client->id,
            'usuario_id' => $this->user->id,
            'item_catalogo_id' => $this->itemCatalogo->id,
            'estado' => 'reservado',
        ]);

        $turno = Turno::first();
        $this->assertEquals($fechaInicio, $turno->fecha_hora_inicio);
        $this->assertEquals($fechaInicio->copy()->addMinutes(30), $turno->fecha_hora_fin);
    }

    public function test_staff_can_create_turno_with_new_client()
    {
        $fechaInicio = Carbon::today()->setTime(15, 0);

        $response = $this->actingAs($this->user)->post(route('turnos.store'), [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'phone' => '0987654321',
            'item_catalogo_id' => $this->itemCatalogo->id,
            'fecha_hora_inicio' => $fechaInicio->format('Y-m-d\TH:i'),
        ]);

        $response->assertRedirect(route('turnos.index'));

        $this->assertDatabaseHas('clients', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'phone' => '0987654321',
        ]);

        $newClient = Client::where('phone', '0987654321')->first();

        $this->assertDatabaseHas('turnos', [
            'cliente_id' => $newClient->id,
        ]);
    }

    public function test_cannot_create_turno_outside_business_hours()
    {
        $fechaInicio = Carbon::today()->setTime(11, 0); // Abre a las 12

        $response = $this->actingAs($this->user)->post(route('turnos.store'), [
            'client_id' => $this->client->id,
            'item_catalogo_id' => $this->itemCatalogo->id,
            'fecha_hora_inicio' => $fechaInicio->format('Y-m-d\TH:i'),
        ]);

        $response->assertSessionHasErrors('fecha_hora_inicio');
        $this->assertDatabaseCount('turnos', 0);
    }

    public function test_cannot_create_overlapping_turno()
    {
        $fechaInicio = Carbon::today()->setTime(14, 0);

        // Crear turno de 14:00 a 14:30
        Turno::create([
            'barberia_id' => $this->barberia->id,
            'cliente_id' => $this->client->id,
            'usuario_id' => $this->user->id,
            'item_catalogo_id' => $this->itemCatalogo->id,
            'fecha_hora_inicio' => $fechaInicio,
            'fecha_hora_fin' => $fechaInicio->copy()->addMinutes(30),
            'estado' => 'reservado',
        ]);

        // Intentar crear otro de 14:15 a 14:45
        $fechaSuperpuesta = Carbon::today()->setTime(14, 15);

        $response = $this->actingAs($this->user)->post(route('turnos.store'), [
            'client_id' => $this->client->id,
            'item_catalogo_id' => $this->itemCatalogo->id,
            'fecha_hora_inicio' => $fechaSuperpuesta->format('Y-m-d\TH:i'),
        ]);

        $response->assertSessionHasErrors('fecha_hora_inicio');
        $this->assertDatabaseCount('turnos', 1);
    }
}
