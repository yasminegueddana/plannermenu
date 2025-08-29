<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer les rôles nécessaires
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);
    }

    /** @test */
    public function admin_can_access_user_management_page()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee('Gestion des Utilisateurs');
    }

    /** @test */
    public function regular_user_cannot_access_user_management_page()
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_user_management_page()
    {
        $response = $this->get('/admin/users');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function admin_can_view_all_users()
    {
        $admin = User::factory()->create(['name' => 'Admin User']);
        $admin->assignRole('admin');
        
        $user1 = User::factory()->create(['name' => 'User One']);
        $user2 = User::factory()->create(['name' => 'User Two']);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee('User One');
        $response->assertSee('User Two');
    }

    /** @test */
    public function admin_can_delete_user()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $userToDelete = User::factory()->create();

        $response = $this->actingAs($admin)
                         ->delete("/admin/users/{$userToDelete->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $userToDelete->id]);
    }

    /** @test */
    public function admin_cannot_delete_themselves()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)
                         ->delete("/admin/users/{$admin->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    /** @test */
    public function regular_user_cannot_delete_users()
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        
        $otherUser = User::factory()->create();

        $response = $this->actingAs($user)
                         ->delete("/admin/users/{$otherUser->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $otherUser->id]);
    }
}
