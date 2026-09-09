<?php

namespace Tests\Feature;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/registro-cliente');

        $response->assertStatus(200);
    }

    public function test_new_clients_can_register(): void
    {
        $response = $this->post('/registro-cliente', [
            'first_name' => 'Test',
            'last_name' => 'Client',
            'phone' => '1155554444',
        ]);

        $this->assertDatabaseHas('clients', [
            'phone' => '1155554444',
        ]);

        $client = Client::where('phone', '1155554444')->first();

        $response->assertSessionHas('client_id', $client->id);
        $response->assertSessionHas('status');
        $response->assertRedirect('/');
    }

    public function test_clients_cannot_register_with_duplicate_phone(): void
    {
        Client::create([
            'first_name' => 'Existing',
            'last_name' => 'Client',
            'phone' => '1155554444',
        ]);

        $response = $this->post('/registro-cliente', [
            'first_name' => 'New',
            'last_name' => 'Client',
            'phone' => '1155554444',
        ]);

        $response->assertSessionHasErrors(['phone']);
        $this->assertDatabaseCount('clients', 1);
    }

    public function test_clients_cannot_register_with_invalid_phone_format(): void
    {
        $response = $this->post('/registro-cliente', [
            'first_name' => 'Test',
            'last_name' => 'Client',
            'phone' => 'invalid-phone-letters',
        ]);

        $response->assertSessionHasErrors(['phone']);
    }
}
