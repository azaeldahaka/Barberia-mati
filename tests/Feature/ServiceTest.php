<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_services_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/services');

        $response->assertOk();
    }

    public function test_service_can_be_created(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/services', [
            'name' => 'Corte Clásico',
            'duration_minutes' => 30,
            'price' => 5000,
        ]);

        $response->assertRedirect('/services');

        $this->assertDatabaseHas('services', [
            'name' => 'Corte Clásico',
            'duration_minutes' => 30,
            'price' => 5000,
        ]);
    }

    public function test_service_validation_rules(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/services', [
            'name' => '',
            'duration_minutes' => 0,
            'price' => -100,
        ]);

        $response->assertSessionHasErrors(['name', 'duration_minutes', 'price']);
    }

    public function test_service_can_be_updated(): void
    {
        $user = User::factory()->create();
        $service = Service::create([
            'name' => 'Corte',
            'duration_minutes' => 20,
            'price' => 2000,
        ]);

        $response = $this->actingAs($user)->put("/services/{$service->id}", [
            'name' => 'Corte Premium',
            'duration_minutes' => 40,
            'price' => 6000,
        ]);

        $response->assertRedirect('/services');

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Corte Premium',
            'duration_minutes' => 40,
            'price' => 6000,
        ]);
    }

    public function test_service_can_be_soft_deleted(): void
    {
        $user = User::factory()->create();
        $service = Service::create([
            'name' => 'Barba',
            'duration_minutes' => 15,
            'price' => 1500,
        ]);

        $response = $this->actingAs($user)->delete("/services/{$service->id}");

        $response->assertRedirect('/services');

        $this->assertSoftDeleted('services', [
            'id' => $service->id,
        ]);
    }
}
