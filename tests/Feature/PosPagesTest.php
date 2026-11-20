<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->make(['id' => 999999]));
    }

    public function test_four_pages_and_navigation_work(): void
    {
        $this->seed(DatabaseSeeder::class);
        foreach (['/' => 'Welcome', '/about' => 'About', '/customers' => 'Customer Accounts', '/users' => 'User Accounts'] as $path => $heading) {
            $response = $this->get($path)->assertOk()->assertSeeText($heading);
            foreach (['home', 'about', 'customers.index', 'users.index'] as $route) {
                $response->assertSee('href="'.route($route).'"', false);
            }
            $response->assertSee('aria-current="page"', false);
        }
    }

    public function test_seeded_tables_display_all_records_and_columns(): void
    {
        $this->seed(DatabaseSeeder::class);
        $customers = $this->get('/customers')->assertOk()->assertSeeInOrder(['Full Name', 'Email', 'Phone']);
        foreach (Customer::all() as $customer) {
            $customers->assertSeeText($customer->full_name)->assertSeeText($customer->email)->assertSeeText($customer->phone);
        }
        $users = $this->get('/users')->assertOk()->assertSeeInOrder(['Username', 'Full Name', 'Role']);
        foreach (User::all() as $user) {
            $users->assertSeeText($user->username)->assertSeeText($user->full_name)->assertSeeText($user->role);
        }
        $this->assertSame(6, substr_count($customers->getContent(), '<tr>'));
        $this->assertSame(6, substr_count($users->getContent(), '<tr>'));
    }

    public function test_pages_reflect_database_inserts_updates_and_deletes(): void
    {
        $customer = Customer::create(['full_name' => 'Database Customer', 'email' => 'db@example.com', 'phone' => '09123456789']);
        $user = User::factory()->create(['username' => 'dbstaff', 'full_name' => 'Database Staff', 'role' => 'Manager']);
        $this->get('/customers')->assertOk()->assertSeeText('Database Customer');
        $this->get('/users')->assertOk()->assertSeeText('dbstaff')->assertSeeText('Database Staff');
        $customer->update(['full_name' => 'Updated Customer']);
        $user->update(['full_name' => 'Updated Staff']);
        $this->get('/customers')->assertSeeText('Updated Customer')->assertDontSeeText('Database Customer');
        $this->get('/users')->assertSeeText('Updated Staff')->assertDontSeeText('Database Staff');
        $customer->delete();
        $user->delete();
        $this->get('/customers')->assertDontSeeText('Updated Customer');
        $this->get('/users')->assertDontSeeText('Updated Staff');
    }

    public function test_record_values_are_escaped_in_both_pages(): void
    {
        $unsafe = '<script>alert("example")</script>';
        Customer::create(['full_name' => $unsafe, 'email' => 'example@example.com', 'phone' => '0917']);
        User::factory()->create(['full_name' => $unsafe]);
        $this->get('/customers')->assertOk()->assertSee($unsafe)->assertDontSee($unsafe, false);
        $this->get('/users')->assertOk()->assertSee($unsafe)->assertDontSee($unsafe, false);
    }

    public function test_empty_database_displays_empty_messages(): void
    {
        $this->get('/customers')->assertOk()->assertSeeText('No customers found.');
        $this->get('/users')->assertOk()->assertSeeText('No users found.');
    }
}
