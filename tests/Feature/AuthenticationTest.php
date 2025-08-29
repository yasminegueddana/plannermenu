<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function user_cannot_login_with_invalid_email()
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    #[Test]
    public function user_cannot_login_with_wrong_password()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correctpassword'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    #[Test]
    public function user_cannot_login_with_empty_fields()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    #[Test]
    public function user_cannot_access_dashboard_without_authentication()
    {
        $response = $this->get('/dashboard');
        
        $response->assertRedirect('/login');
    }

    #[Test]
    public function authenticated_user_cannot_access_login_page()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/login');
        
        $response->assertRedirect('/dashboard');
    }
}
