<?php

namespace Tests\Feature;

use App\Models\Barberia;
use App\Models\ItemCatalogo;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_services_page_is_displayed(): void
    {
        $barberia = Barberia::factory()->create();
        $user = User::factory()->create(['barberia_id' => $barberia->id]);

        $response = $this->actingAs($user)->get('/services');

        $response->assertOk();
    }

    public function test_service_can_be_created(): void
    {
        $barberia = Barberia::factory()->create();
        $user = User::factory()->create(['barberia_id' => $barberia->id]);

        $response = $this->actingAs($user)->post('/services', [
            'name' => 'Corte Clásico',
            'duration_minutes' => 30,
            'price' => 5000,
        ]);

        $response->assertRedirect('/services');

        $this->assertDatabaseHas('item_catalogos', [
            'nombre' => 'Corte Clásico',
            'duracion_minutos' => 30,
            'precio' => 5000,
            'tipo' => 'servicio',
            'barberia_id' => $barberia->id,
        ]);

        $item = ItemCatalogo::where('nombre', 'Corte Clásico')->first();

        $this->assertDatabaseHas('services', [
            'item_catalogo_id' => $item->id,
            'cuenta_para_fidelizacion' => 0,
        ]);
    }

    public function test_service_validation_rules(): void
    {
        $barberia = Barberia::factory()->create();
        $user = User::factory()->create(['barberia_id' => $barberia->id]);

        $response = $this->actingAs($user)->post('/services', [
            'name' => '',
            'duration_minutes' => 0,
            'price' => -100,
        ]);

        $response->assertSessionHasErrors(['name', 'duration_minutes', 'price']);
    }

    public function test_service_can_be_updated(): void
    {
        $barberia = Barberia::factory()->create();
        $user = User::factory()->create(['barberia_id' => $barberia->id]);

        $item = ItemCatalogo::create([
            'barberia_id' => $barberia->id,
            'tipo' => 'servicio',
            'nombre' => 'Corte',
            'duracion_minutos' => 20,
            'precio' => 2000,
        ]);
        $service = Service::create([
            'item_catalogo_id' => $item->id,
        ]);

        $response = $this->actingAs($user)->put("/services/{$service->id}", [
            'name' => 'Corte Premium',
            'duration_minutes' => 40,
            'price' => 6000,
        ]);

        $response->assertRedirect('/services');

        $this->assertDatabaseHas('item_catalogos', [
            'id' => $item->id,
            'nombre' => 'Corte Premium',
            'duracion_minutos' => 40,
            'precio' => 6000,
        ]);
    }

    public function test_service_can_be_soft_deleted(): void
    {
        $barberia = Barberia::factory()->create();
        $user = User::factory()->create(['barberia_id' => $barberia->id]);

        $item = ItemCatalogo::create([
            'barberia_id' => $barberia->id,
            'tipo' => 'servicio',
            'nombre' => 'Barba',
            'duracion_minutos' => 15,
            'precio' => 1500,
        ]);
        $service = Service::create([
            'item_catalogo_id' => $item->id,
        ]);

        $response = $this->actingAs($user)->delete("/services/{$service->id}");

        $response->assertRedirect('/services');

        $this->assertSoftDeleted('services', [
            'id' => $service->id,
        ]);

        $this->assertSoftDeleted('item_catalogos', [
            'id' => $item->id,
        ]);
    }
}
