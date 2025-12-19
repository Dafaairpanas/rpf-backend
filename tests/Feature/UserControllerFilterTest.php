<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserControllerFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'Super Admin']);
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'User']);
    }

    public function test_superadmin_can_access_users_list(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $superAdmin = User::factory()->create(['role_id' => $superAdminRole->id]);

        Sanctum::actingAs($superAdmin);

        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data',
                    'current_page',
                    'per_page',
                ],
            ]);
    }

    public function test_non_superadmin_cannot_access_users_list(): void
    {
        $adminRole = Role::where('name', 'Admin')->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(403);
    }

    public function test_can_search_users_by_name(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $superAdmin = User::factory()->create([
            'role_id' => $superAdminRole->id,
            'name' => 'Super Admin User',
        ]);

        User::factory()->create(['name' => 'John Doe', 'role_id' => $superAdminRole->id]);
        User::factory()->create(['name' => 'Jane Smith', 'role_id' => $superAdminRole->id]);

        Sanctum::actingAs($superAdmin);

        $response = $this->getJson('/api/v1/users?q=John');

        $response->assertStatus(200);

        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('John Doe', $data[0]['name']);
    }

    public function test_can_filter_users_by_role(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $adminRole = Role::where('name', 'Admin')->first();

        $superAdmin = User::factory()->create([
            'role_id' => $superAdminRole->id,
            'name' => 'Super Admin',
        ]);

        User::factory()->create(['role_id' => $adminRole->id, 'name' => 'Admin User']);
        User::factory()->create(['role_id' => $superAdminRole->id, 'name' => 'Another Super']);

        Sanctum::actingAs($superAdmin);

        $response = $this->getJson('/api/v1/users?filter[role_id]=' . $adminRole->id);

        $response->assertStatus(200);

        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('Admin User', $data[0]['name']);
    }

    public function test_can_sort_users(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $superAdmin = User::factory()->create([
            'role_id' => $superAdminRole->id,
            'name' => 'Alpha User',
        ]);

        User::factory()->create(['role_id' => $superAdminRole->id, 'name' => 'Zeta User']);
        User::factory()->create(['role_id' => $superAdminRole->id, 'name' => 'Beta User']);

        Sanctum::actingAs($superAdmin);

        // Sort ascending
        $response = $this->getJson('/api/v1/users?sort=name');
        $data = $response->json('data.data');
        $this->assertEquals('Alpha User', $data[0]['name']);

        // Sort descending
        $response = $this->getJson('/api/v1/users?sort=-name');
        $data = $response->json('data.data');
        $this->assertEquals('Zeta User', $data[0]['name']);
    }

    public function test_can_paginate_users(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $superAdmin = User::factory()->create(['role_id' => $superAdminRole->id]);

        User::factory()->count(10)->create(['role_id' => $superAdminRole->id]);

        Sanctum::actingAs($superAdmin);

        $response = $this->getJson('/api/v1/users?per_page=5');

        $response->assertStatus(200);
        $this->assertEquals(5, $response->json('data.per_page'));
        $this->assertCount(5, $response->json('data.data'));
    }
}
