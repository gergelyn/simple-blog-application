<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson(route('login', [
            'email' => $user->email,
            'password' => 'password',
        ]));

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson(route('login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]));

        $response->assertStatus(422);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $logoutResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('logout'));

        $logoutResponse->assertStatus(200)
            ->assertJson(['message' => 'Logged out']);
    }
}
