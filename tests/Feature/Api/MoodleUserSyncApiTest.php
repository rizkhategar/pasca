<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MoodleUserSyncApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/integrations/moodle/users')
            ->assertUnauthorized();
    }

    public function test_the_endpoint_requires_the_moodle_users_read_ability(): void
    {
        $integrationUser = User::factory()->create();

        Sanctum::actingAs($integrationUser, ['other-ability']);

        $this->getJson('/api/integrations/moodle/users')
            ->assertForbidden();
    }

    public function test_it_returns_paginated_users_with_the_existing_password_hash(): void
    {
        $integrationUser = User::factory()->create();
        $passwordHash = Hash::make('SamePassword123!');
        $sourceUser = User::factory()->create([
            'name' => 'Moodle Sync User',
            'email' => 'sync.user@example.com',
            'password' => $passwordHash,
        ]);

        Sanctum::actingAs($integrationUser, ['moodle-users:read']);

        $this->getJson('/api/integrations/moodle/users?per_page=100')
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('meta.total', 2)
            ->assertJsonFragment([
                'source_id' => $sourceUser->id,
                'name' => 'Moodle Sync User',
                'email' => 'sync.user@example.com',
                'password_hash' => $passwordHash,
                'email_verified' => true,
            ]);
    }

    public function test_it_can_filter_users_by_updated_after(): void
    {
        $integrationUser = User::factory()->create(['updated_at' => now()->subDays(3)]);
        User::factory()->create(['updated_at' => now()->subDays(2)]);
        $recentUser = User::factory()->create(['updated_at' => now()]);

        Sanctum::actingAs($integrationUser, ['moodle-users:read']);

        $updatedAfter = now()->subDay()->toISOString();

        $this->getJson('/api/integrations/moodle/users?updated_after='.urlencode($updatedAfter))
            ->assertOk()
            ->assertJsonFragment(['source_id' => $recentUser->id])
            ->assertJsonMissing(['source_id' => $integrationUser->id]);
    }

    public function test_it_rejects_an_invalid_page_size(): void
    {
        $integrationUser = User::factory()->create();

        Sanctum::actingAs($integrationUser, ['moodle-users:read']);

        $this->getJson('/api/integrations/moodle/users?per_page=101')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('per_page');
    }
}
