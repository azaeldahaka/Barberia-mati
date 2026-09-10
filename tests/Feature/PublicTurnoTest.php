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

class PublicTurnoTest extends TestCase
{
    use RefreshDatabase;

    private Barberia $barberia;

    private User $user;

    private ItemCatalogo $itemCatalogo;

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

        $this->itemCatalogo = ItemCatalogo::create([
            'barberia_id' => $this->barberia->id,
            'tipo' => 'servicio',
            'nombre' => 'Corte Test',
            'precio' => 5000,
            'duracion_minutos' => 30,
        ]);

        $this->client = Client::create([
            'barberia_id' => $this->barberia->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '1234567890',
        ]);
    }

    public function test_can_view_public_booking_page()
    {
        $response = $this->get('/reservar');

        $response->assertStatus(200);
    }

    public function test_booking_without_session_redirects_to_registration_and_saves_pending_turno()
    {
        // Tomorrow at 15:00
        $fechaHoraInicio = Carbon::tomorrow()->setTime(15, 0)->format('Y-m-d\TH:i');

        $response = $this->post('/reservar', [
            'item_catalogo_id' => $this->itemCatalogo->id,
            'fecha_hora_inicio' => $fechaHoraInicio,
        ]);

        $response->assertRedirect('/registro-cliente');
        $this->assertNotNull(session('pending_turno'));
        $this->assertEquals($this->itemCatalogo->id, session('pending_turno.item_catalogo_id'));
        $this->assertDatabaseMissing('turnos', [
            'item_catalogo_id' => $this->itemCatalogo->id,
        ]);
    }

    public function test_booking_with_session_creates_turno_assigned_to_default_user()
    {
        $fechaHoraInicio = Carbon::tomorrow()->setTime(15, 0)->format('Y-m-d\TH:i');

        $response = $this->withSession(['client_id' => $this->client->id])
            ->post('/reservar', [
                'item_catalogo_id' => $this->itemCatalogo->id,
                'fecha_hora_inicio' => $fechaHoraInicio,
            ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('status', 'Tu reserva ha sido confirmada exitosamente. ¡Te esperamos!');

        $this->assertDatabaseHas('turnos', [
            'barberia_id' => $this->barberia->id,
            'cliente_id' => $this->client->id,
            'usuario_id' => $this->user->id,
            'item_catalogo_id' => $this->itemCatalogo->id,
            'fecha_hora_inicio' => Carbon::parse($fechaHoraInicio)->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_booking_outside_business_hours_is_rejected()
    {
        // Tomorrow at 11:00 (before 12:00)
        $fechaHoraInicio = Carbon::tomorrow()->setTime(11, 0)->format('Y-m-d\TH:i');

        $response = $this->withSession(['client_id' => $this->client->id])
            ->post('/reservar', [
                'item_catalogo_id' => $this->itemCatalogo->id,
                'fecha_hora_inicio' => $fechaHoraInicio,
            ]);

        $response->assertSessionHasErrors('fecha_hora_inicio');
        $this->assertDatabaseMissing('turnos', [
            'cliente_id' => $this->client->id,
        ]);
    }

    public function test_booking_overlapping_turno_is_rejected()
    {
        $fechaHoraInicio = Carbon::tomorrow()->setTime(15, 0);

        // Create an existing turn
        Turno::create([
            'barberia_id' => $this->barberia->id,
            'cliente_id' => $this->client->id,
            'usuario_id' => $this->user->id,
            'item_catalogo_id' => $this->itemCatalogo->id,
            'fecha_hora_inicio' => $fechaHoraInicio->copy()->format('Y-m-d H:i:s'),
            'fecha_hora_fin' => $fechaHoraInicio->copy()->addMinutes(30)->format('Y-m-d H:i:s'),
            'estado' => 'reservado',
        ]);

        $response = $this->withSession(['client_id' => $this->client->id])
            ->post('/reservar', [
                'item_catalogo_id' => $this->itemCatalogo->id,
                'fecha_hora_inicio' => $fechaHoraInicio->format('Y-m-d\TH:i'),
            ]);

        $response->assertSessionHasErrors('fecha_hora_inicio');
    }
}
