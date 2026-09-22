<?php

namespace Tests\Feature\Auth;

use App\Models\Barberia;
use App\Models\Client;
use App\Models\ItemCatalogo;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_user_is_seeded_and_can_authenticate(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->post('/login', [
            'email' => 'tester@barberia.com',
            'password' => 'tester123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = auth()->user();
        $this->assertTrue($user->isDemo());
        $this->assertTrue($user->hasRole('Dueño'));
    }

    public function test_demo_user_can_access_dashboard(): void
    {
        $barberia = Barberia::factory()->create();
        $user = User::factory()->create([
            'barberia_id' => $barberia->id,
            'is_demo' => true,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
    }

    public function test_demo_user_cannot_update_profile_information(): void
    {
        $barberia = Barberia::factory()->create();
        $user = User::factory()->create([
            'name' => 'Tester User',
            'email' => 'tester@barberia.com',
            'barberia_id' => $barberia->id,
            'is_demo' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->patch('/profile', [
                'name' => 'Hacked Name',
                'email' => 'hacked@example.com',
            ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHas('error');

        $user->refresh();
        $this->assertSame('Tester User', $user->name);
        $this->assertSame('tester@barberia.com', $user->email);
    }

    public function test_demo_user_cannot_change_password(): void
    {
        $barberia = Barberia::factory()->create();
        $user = User::factory()->create([
            'barberia_id' => $barberia->id,
            'password' => Hash::make('tester123'),
            'is_demo' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/password', [
                'current_password' => 'tester123',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHas('error');

        $user->refresh();
        $this->assertTrue(Hash::check('tester123', $user->password));
    }

    public function test_demo_user_cannot_delete_account(): void
    {
        $barberia = Barberia::factory()->create();
        $user = User::factory()->create([
            'barberia_id' => $barberia->id,
            'password' => Hash::make('tester123'),
            'is_demo' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'tester123',
            ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHas('error');

        $this->assertNotNull($user->fresh());
        $this->assertAuthenticatedAs($user);
    }

    public function test_demo_user_cannot_delete_services(): void
    {
        $barberia = Barberia::factory()->create();
        $user = User::factory()->create([
            'barberia_id' => $barberia->id,
            'is_demo' => true,
        ]);

        $item = ItemCatalogo::create([
            'barberia_id' => $barberia->id,
            'nombre' => 'Corte Demo',
            'tipo' => 'servicio',
            'precio' => 10000,
            'duracion_minutos' => 30,
        ]);

        $service = Service::create([
            'item_catalogo_id' => $item->id,
            'cuenta_para_fidelizacion' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->from('/services')
            ->delete("/services/{$service->id}");

        $response->assertRedirect('/services');
        $response->assertSessionHas('error');

        $this->assertNotNull($service->fresh());
    }

    public function test_demo_user_can_schedule_turnos(): void
    {
        $barberia = Barberia::factory()->create([
            'horario_apertura' => '08:00',
            'horario_cierre' => '20:00',
        ]);
        $user = User::factory()->create([
            'barberia_id' => $barberia->id,
            'is_demo' => true,
        ]);
        $client = Client::create([
            'first_name' => 'Juan',
            'last_name' => 'Perez',
            'phone' => '1122334455',
        ]);
        $item = ItemCatalogo::create([
            'barberia_id' => $barberia->id,
            'nombre' => 'Corte',
            'tipo' => 'servicio',
            'precio' => 10000,
            'duracion_minutos' => 30,
        ]);

        $start = Carbon::tomorrow()->setTime(10, 0);

        $response = $this
            ->actingAs($user)
            ->post('/turnos', [
                'client_id' => $client->id,
                'item_catalogo_id' => $item->id,
                'fecha_hora_inicio' => $start->format('Y-m-d H:i:s'),
            ]);

        $response->assertSessionHas('status', 'Turno agendado exitosamente.');
        $this->assertDatabaseHas('turnos', [
            'barberia_id' => $barberia->id,
            'cliente_id' => $client->id,
            'usuario_id' => $user->id,
            'item_catalogo_id' => $item->id,
        ]);
    }
}
