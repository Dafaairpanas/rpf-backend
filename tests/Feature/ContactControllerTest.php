<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_submit_contact_form(): void
    {
        $response = $this->postJson('/api/v1/contact', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '081234567890',
            'message' => 'Hello, this is a test message.',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'created_at',
                ],
            ]);

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'status' => 'new',
        ]);
    }

    public function test_contact_form_requires_name(): void
    {
        $response = $this->postJson('/api/v1/contact', [
            'email' => 'john@example.com',
            'message' => 'Hello',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_contact_form_requires_email(): void
    {
        $response = $this->postJson('/api/v1/contact', [
            'name' => 'John Doe',
            'message' => 'Hello',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_contact_form_requires_valid_email(): void
    {
        $response = $this->postJson('/api/v1/contact', [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'message' => 'Hello',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_contact_form_requires_message(): void
    {
        $response = $this->postJson('/api/v1/contact', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    public function test_phone_is_optional(): void
    {
        $response = $this->postJson('/api/v1/contact', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'Hello without phone',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'John Doe',
            'phone' => null,
        ]);
    }
}
