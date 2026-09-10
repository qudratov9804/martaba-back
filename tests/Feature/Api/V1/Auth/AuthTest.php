<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('a user can register via the api', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonPath('data.user.email', 'jane@example.com')
        ->assertJsonPath('data.user.roles.0', 'student')
        ->assertJsonStructure(['data' => ['user', 'token']]);

    $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
});

test('registration fails with invalid data', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => '',
        'email' => 'not-an-email',
        'password' => 'short',
        'password_confirmation' => 'mismatch',
    ]);

    $response->assertStatus(422)
        ->assertJson(['success' => false])
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

test('a user can login via the api and receive a token', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJson(['success' => true])
        ->assertJsonStructure(['data' => ['user', 'token']]);
});

test('login fails with invalid credentials', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422)->assertJson(['success' => false]);
});

test('an authenticated user can fetch their profile via me endpoint', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/auth/me');

    $response->assertOk()->assertJsonPath('data.email', $user->email);
});

test('a guest cannot fetch the me endpoint', function () {
    $this->getJson('/api/v1/auth/me')->assertStatus(401)->assertJson(['success' => false]);
});

test('a user can logout and their token is revoked', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/logout');

    $response->assertOk()->assertJson(['success' => true]);

    // Force the auth guard to re-resolve the user instead of reusing the
    // cached guard instance from the previous request in this same process.
    $this->app['auth']->forgetGuards();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/auth/me')
        ->assertStatus(401);
});
