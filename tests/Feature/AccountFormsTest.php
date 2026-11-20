<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountFormsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->make(['id' => 999999]));
    }

    public function test_customer_validation_preserves_input_and_valid_customer_can_be_created(): void
    {
        $this->get('/customers/create')->assertOk();
        $this->from('/customers/create')->post('/customers', ['full_name' => 'New Customer', 'email' => 'invalid'])
            ->assertRedirect('/customers/create')->assertSessionHasErrors('email')
            ->assertSessionHasInput('full_name', 'New Customer');
        $this->get('/customers/create')->assertSee('value="New Customer"', false)->assertSee('email-error');
        $this->assertDatabaseCount('customers', 0);
        $this->post('/customers', ['full_name' => '', 'email' => ''])->assertSessionHasErrors(['full_name', 'email']);
        $this->post('/customers', ['full_name' => 'New Customer', 'email' => 'new@example.com'])
            ->assertRedirect('/customers')->assertSessionHas('status');
        $this->assertDatabaseHas('customers', ['full_name' => 'New Customer', 'email' => 'new@example.com', 'phone' => '']);
    }

    public function test_user_requires_unique_username_and_full_name(): void
    {
        $this->get('/users/create')->assertOk();
        $this->post('/users', ['username' => '', 'full_name' => '', 'role' => 'Staff'])
            ->assertSessionHasErrors(['username', 'full_name']);
        $data = ['username' => 'newstaff', 'full_name' => 'New Staff', 'role' => 'Staff'];
        $this->post('/users', $data)->assertRedirect('/users');
        $this->from('/users/create')->post('/users', $data)->assertSessionHasErrors('username')
            ->assertSessionHasInput('full_name', 'New Staff');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_edit_forms_prefill_and_update_both_account_types(): void
    {
        $customer = Customer::create(['full_name' => 'Original Customer', 'email' => 'original@example.com', 'phone' => '0917']);
        $user = User::factory()->create(['username' => 'original', 'full_name' => 'Original User', 'role' => 'Staff']);
        $this->get("/customers/{$customer->id}/edit")->assertOk()->assertSee('value="Original Customer"', false);
        $this->put("/customers/{$customer->id}", ['full_name' => 'Updated Customer', 'email' => $customer->email, 'phone' => '0999'])
            ->assertRedirect('/customers');
        $this->get("/users/{$user->id}/edit")->assertOk()->assertSee('value="original"', false)->assertSee('multipart/form-data');
        $this->put("/users/{$user->id}", ['username' => 'original', 'full_name' => 'Updated User', 'role' => 'Manager'])
            ->assertRedirect('/users');
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'full_name' => 'Updated Customer', 'phone' => '0999']);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'full_name' => 'Updated User', 'role' => 'Manager']);
        $this->get('/customers/9999/edit')->assertNotFound();
        $this->get('/users/9999/edit')->assertNotFound();
        $this->put('/users/9999', [])->assertNotFound();
    }

    public function test_conflicts_cannot_overwrite_another_account(): void
    {
        $first = User::factory()->create(['username' => 'first']);
        $second = User::factory()->create(['username' => 'second']);
        $this->put("/users/{$second->id}", ['username' => $first->username, 'full_name' => 'Changed', 'role' => 'Staff'])
            ->assertSessionHasErrors('username');
        $this->assertSame('second', $second->fresh()->username);
        $a = Customer::create(['full_name' => 'First', 'email' => 'first@example.com', 'phone' => '']);
        $b = Customer::create(['full_name' => 'Second', 'email' => 'second@example.com', 'phone' => '']);
        $this->put("/customers/{$b->id}", ['full_name' => 'Changed', 'email' => $a->email])->assertSessionHasErrors('email');
        $this->assertSame('Second', $b->fresh()->full_name);
    }

    public function test_avatar_is_resized_stored_displayed_preserved_and_replaced(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'Staff']);
        $data = ['username' => $user->username, 'full_name' => $user->full_name, 'role' => 'Staff'];
        $this->get('/users')->assertSee('images/avatar-placeholder.svg');
        $this->put("/users/{$user->id}", [...$data, 'avatar' => UploadedFile::fake()->image('portrait.png', 800, 400)])
            ->assertRedirect('/users')->assertSessionHasNoErrors();
        $path = $user->fresh()->avatar;
        $this->assertMatchesRegularExpression('~^avatars/[a-f0-9-]+\.jpg$~', $path);
        Storage::disk('public')->assertExists($path);
        $info = getimagesize(Storage::disk('public')->path($path));
        $this->assertSame([256, 256, IMAGETYPE_JPEG], array_slice($info, 0, 3));
        $this->get('/users')->assertSee(Storage::disk('public')->url($path), false);
        $this->put("/users/{$user->id}", $data)->assertSessionHasNoErrors();
        $this->assertSame($path, $user->fresh()->avatar);
        $this->put("/users/{$user->id}", [...$data, 'avatar' => UploadedFile::fake()->image('replacement.jpg')])->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($path);
        Storage::disk('public')->assertExists($user->fresh()->avatar);
    }

    public function test_invalid_uploads_do_not_change_the_record_or_existing_avatar(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/previous.jpg', 'existing');
        $user = User::factory()->create(['avatar' => 'avatars/previous.jpg', 'role' => 'Staff']);
        $data = ['username' => $user->username, 'full_name' => 'Do not save', 'role' => 'Staff'];
        foreach ([
            UploadedFile::fake()->create('document.txt', 1, 'text/plain'),
            UploadedFile::fake()->image('large.jpg')->size(2049),
            UploadedFile::fake()->createWithContent('fake.jpg', 'not an image'),
        ] as $file) {
            $this->from("/users/{$user->id}/edit")->put("/users/{$user->id}", [...$data, 'avatar' => $file])
                ->assertRedirect("/users/{$user->id}/edit")->assertSessionHasErrors('avatar')
                ->assertSessionHasInput('full_name', 'Do not save');
            $this->assertNotSame('Do not save', $user->fresh()->full_name);
            $this->assertSame('avatars/previous.jpg', $user->fresh()->avatar);
        }
        Storage::disk('public')->assertExists('avatars/previous.jpg');
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }
}
