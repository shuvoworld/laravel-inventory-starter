<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SidebarPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_link_visible_when_user_has_permission(): void
    {
        $user = User::factory()->create();
        Permission::create(['name' => 'users.view', 'guard_name' => 'web']);
        $user->givePermissionTo('users.view');

        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Users');
    }

    public function test_users_link_hidden_when_user_lacks_permission(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertDontSee('Users');
    }
}
