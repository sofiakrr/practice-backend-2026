<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_bookings_endpoint_requires_auth_token(): void
    {
        $this->getJson('/api/bookings')
            ->assertStatus(401);
    }

    public function test_bookings_endpoint_is_available_with_valid_token(): void
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();
        $resource = $this->createResource();

        Booking::create([
            'user_id' => $user->id,
            'resource_id' => $resource->id,
            'date' => now()->addDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'active',
        ]);

        Booking::create([
            'user_id' => $otherUser->id,
            'resource_id' => $resource->id,
            'date' => now()->addDay()->toDateString(),
            'start_time' => '12:00',
            'end_time' => '13:00',
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/bookings', $this->authHeaders($user));

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['user_id' => $user->id]);
        $response->assertJsonMissing(['user_id' => $otherUser->id]);
    }

    public function test_user_can_create_booking(): void
    {
        $user = $this->createUser();
        $resource = $this->createResource();
        $date = now()->addDay()->toDateString();

        $response = $this->postJson('/api/bookings', [
            'resource_id' => $resource->id,
            'date' => $date,
            'start_time' => '09:00',
            'end_time' => '10:00',
        ], $this->authHeaders($user));

        $response->assertStatus(201);
        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'resource_id' => $resource->id,
            'date' => $date,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'status' => 'active',
        ]);
    }

    public function test_booking_creation_fails_on_time_overlap(): void
    {
        $user = $this->createUser();
        $resource = $this->createResource();
        $date = now()->addDay()->toDateString();

        Booking::create([
            'user_id' => $user->id,
            'resource_id' => $resource->id,
            'date' => $date,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/bookings', [
            'resource_id' => $resource->id,
            'date' => $date,
            'start_time' => '11:00',
            'end_time' => '13:00',
        ], $this->authHeaders($this->createUser()));

        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
    }

    public function test_booking_creation_allows_adjacent_time_without_overlap(): void
    {
        $user = $this->createUser();
        $resource = $this->createResource();
        $date = now()->addDay()->toDateString();

        Booking::create([
            'user_id' => $user->id,
            'resource_id' => $resource->id,
            'date' => $date,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/bookings', [
            'resource_id' => $resource->id,
            'date' => $date,
            'start_time' => '12:00',
            'end_time' => '13:00',
        ], $this->authHeaders($this->createUser()));

        $response->assertStatus(201);
        $this->assertDatabaseHas('bookings', [
            'resource_id' => $resource->id,
            'date' => $date,
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);
    }

    public function test_booking_creation_fails_for_inactive_resource(): void
    {
        $user = $this->createUser();
        $resource = $this->createResource(false);

        $response = $this->postJson('/api/bookings', [
            'resource_id' => $resource->id,
            'date' => now()->addDay()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
        ], $this->authHeaders($user));

        $response->assertStatus(422);
        $response->assertJsonStructure(['message']);
    }

    private function createUser(string $role = 'user'): User
    {
        return User::create([
            'name' => 'Test User',
            'email' => uniqid('user_', true).'@example.com',
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }

    private function createResource(bool $isActive = true): Resource
    {
        return Resource::create([
            'name' => uniqid('table_', true),
            'description' => 'Table for tests',
            'type' => 'standard',
            'capacity' => 4,
            'location' => 'Main hall',
            'price_per_hour' => 350,
            'is_active' => $isActive,
        ]);
    }

    private function authHeaders(User $user): array
    {
        $token = auth('api')->login($user);

        return [
            'Authorization' => 'Bearer '.$token,
        ];
    }
}
