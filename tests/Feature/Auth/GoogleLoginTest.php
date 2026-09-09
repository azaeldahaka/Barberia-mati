<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirects_to_google()
    {
        config()->set('services.google.client_id', 'mock-client-id');
        config()->set('services.google.client_secret', 'mock-client-secret');
        config()->set('services.google.redirect', '/auth/google/callback');

        $response = $this->get('/auth/google/redirect');

        $response->assertRedirectContains('accounts.google.com/o/oauth2/auth');
    }

    public function test_authenticates_and_redirects_user()
    {
        config()->set('services.google.client_id', 'mock-client-id');
        config()->set('services.google.client_secret', 'mock-client-secret');
        config()->set('services.google.redirect', '/auth/google/callback');

        // Mock Socialite User
        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('1234567890');
        $abstractUser->shouldReceive('getName')->andReturn('Test User');
        $abstractUser->shouldReceive('getEmail')->andReturn('test@example.com');

        // Mock Socialite Provider
        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        // Act
        $response = $this->get('/auth/google/callback');

        // Assert
        $this->assertAuthenticated();
        $response->assertRedirect('/');

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'google_id' => '1234567890',
        ]);
    }
}
