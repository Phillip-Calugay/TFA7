<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResourceAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_resource_action_requires_authentication(): void
    {
        $customer = Customer::create(['full_name' => 'Protected', 'email' => 'protected@example.com', 'phone' => '']);
        $user = User::factory()->create();
        foreach (['customers' => $customer->id, 'users' => $user->id] as $resource => $id) {
            foreach ([['GET', "/$resource"], ['GET', "/$resource/create"], ['POST', "/$resource"],
                ['GET', "/$resource/$id"], ['GET', "/$resource/$id/edit"], ['PUT', "/$resource/$id"],
                ['PATCH', "/$resource/$id"], ['DELETE', "/$resource/$id"]] as [$method, $uri]) {
                $this->call($method, $uri)->assertRedirect(route('login'));
            }
        }
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'full_name' => 'Protected']);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_login_returns_to_intended_page_and_logout_revokes_access(): void
    {
        $user = User::factory()->create();
        $this->get('/customers')->assertRedirect('/login');
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/customers');
        $this->get('/customers')->assertOk();
        $this->get('/users')->assertOk();
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        $this->get('/customers')->assertRedirect('/login');
        $this->get('/users')->assertRedirect('/login');
    }

    public function test_registration_hashes_password_rejects_conflicts_and_ignores_role_injection(): void
    {
        $data = ['full_name' => 'Registered Staff', 'username' => 'registered', 'email' => 'registered@example.com',
            'password' => 'LocalTest!2026', 'password_confirmation' => 'LocalTest!2026', 'role' => 'Administrator'];
        $this->post('/register', $data)->assertRedirect('/dashboard');
        $user = User::where('username', 'registered')->firstOrFail();
        $this->assertTrue(Hash::check($data['password'], $user->password));
        $this->assertSame('Staff', $user->role);
        $this->assertArrayNotHasKey('password', $user->toArray());
        $this->post('/logout');
        $this->post('/register', $data)->assertSessionHasErrors(['username', 'email']);
        $this->post('/register', [...$data, 'username' => 'another', 'email' => 'another@example.com', 'password_confirmation' => 'wrong'])
            ->assertSessionHasErrors('password');
    }

    public function test_signed_in_user_can_view_and_delete_resources_and_avatar(): void
    {
        Storage::fake('public');
        $actor = User::factory()->create();
        $this->actingAs($actor);
        $customer = Customer::create(['full_name' => 'Delete Me', 'email' => 'delete@example.com', 'phone' => '']);
        $user = User::factory()->create(['avatar' => 'avatars/delete.jpg']);
        Storage::disk('public')->put($user->avatar, 'image');
        $this->get("/customers/{$customer->id}")->assertOk()->assertSeeText('Delete Me');
        $this->get("/users/{$user->id}")->assertOk()->assertSeeText($user->full_name);
        $this->delete("/customers/{$customer->id}")->assertRedirect('/customers');
        $this->delete("/users/{$user->id}")->assertRedirect('/users');
        $this->assertModelMissing($customer);
        $this->assertModelMissing($user);
        Storage::disk('public')->assertMissing('avatars/delete.jpg');
        $this->delete("/users/{$actor->id}")->assertSessionHasErrors('user');
        $this->assertModelExists($actor);
    }

    public function test_failed_logins_are_throttled(): void
    {
        $user = User::factory()->create();
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        }
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
