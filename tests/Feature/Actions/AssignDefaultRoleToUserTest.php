<?php

namespace Tests\Feature\Actions;

use App\Actions\Roles\AssignDefaultRoleToUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignDefaultRoleToUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_assigns_default_role_to_user(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->hasRole('Dueño'));

        $action = new AssignDefaultRoleToUser;
        $action->handle($user);

        // We need to refresh the user's relationship
        $user->load('roles');

        $this->assertTrue($user->hasRole('Dueño'));
    }
}
