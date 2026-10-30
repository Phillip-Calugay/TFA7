<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosAccountSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrations_and_seeder_support_repeated_seeding(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('customers', 5);
        $this->assertDatabaseCount('users', 5);
        $this->assertDatabaseHas('customers', ['full_name' => 'Ana Reyes', 'email' => 'ana.reyes@example.com']);
        $this->assertDatabaseHas('users', ['username' => 'stock1', 'role' => 'Inventory Staff']);
    }

    public function test_usernames_are_unique_in_the_database(): void
    {
        User::factory()->create(['username' => 'duplicate']);
        $this->expectException(QueryException::class);
        User::factory()->create(['username' => 'duplicate']);
    }

    public function test_pos_migrations_can_be_rolled_back_and_reapplied(): void
    {
        $this->artisan('migrate:rollback', ['--step' => 2, '--force' => true])->assertSuccessful();
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('customers', 5);
        $this->assertDatabaseCount('users', 5);
    }
}
