<?php

namespace Tests\Feature\Account;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'admin.test@modeva.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_user_can_view_change_password_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.password.edit'));

        $response->assertStatus(200);
        $response->assertSee('Ubah Password');
        $response->assertSee('Password Saat Ini');
        $response->assertSee('Password Baru');
    }

    public function test_user_can_change_password_with_valid_credentials(): void
    {
        $response = $this->actingAs($this->user)->put(route('admin.password.update'), [
            'current_password' => 'password123',
            'password' => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->user->refresh();
        $this->assertTrue(Hash::check('newpassword456', $this->user->password));
    }

    public function test_change_password_fails_with_wrong_current_password(): void
    {
        $response = $this->actingAs($this->user)->put(route('admin.password.update'), [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->user->refresh();
        $this->assertTrue(Hash::check('password123', $this->user->password));
    }

    public function test_change_password_fails_when_confirmation_does_not_match(): void
    {
        $response = $this->actingAs($this->user)->put(route('admin.password.update'), [
            'current_password' => 'password123',
            'password' => 'newpassword456',
            'password_confirmation' => 'different123',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_change_password_fails_when_same_as_current_password(): void
    {
        $response = $this->actingAs($this->user)->put(route('admin.password.update'), [
            'current_password' => 'password123',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_change_password_is_throttled_after_five_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($this->user)->put(route('admin.password.update'), [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword456',
                'password_confirmation' => 'newpassword456',
            ]);
        }

        // Percobaan ke-6 harus di-throttle (HTTP 429 Too Many Requests)
        $response = $this->actingAs($this->user)->put(route('admin.password.update'), [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ]);

        $response->assertStatus(429);
    }
}
