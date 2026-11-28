<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('MODEVA');
        $response->assertSee('Buat Akun Baru');
        $response->assertSee('Daftar Akun');
    }

    public function test_new_customer_can_register_and_is_authenticated(): void
    {
        Event::fake([Registered::class]);

        $response = $this->post(route('register.store'), [
            'name' => 'Siti Nurhaliza',
            'email' => 'siti@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();

        $user = User::where('email', 'siti@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('Siti Nurhaliza', $user->name);
        $this->assertSame('pelanggan', $user->role);
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('password123', $user->password));

        Event::assertDispatched(Registered::class);
        $response->assertRedirect(route('pelanggan.dashboard'));
    }

    public function test_registration_with_intended_url_redirects_to_intended(): void
    {
        session()->put('url.intended', 'http://localhost/pesanan/MDV-123/pembayaran');

        $response = $this->post(route('register.store'), [
            'name' => 'Rina Wijaya',
            'email' => 'rina@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('http://localhost/pesanan/MDV-123/pembayaran');
    }

    public function test_registration_ignores_role_input_and_always_assigns_pelanggan(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Hacker Admin',
            'email' => 'hacker@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $user = User::where('email', 'hacker@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('pelanggan', $user->role);
    }

    public function test_registration_fails_when_email_already_registered(): void
    {
        User::factory()->create([
            'email' => 'duplikat@example.com',
        ]);

        $response = $this->post(route('register.store'), [
            'name' => 'User Lain',
            'email' => 'duplikat@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email' => 'Alamat email ini sudah terdaftar.']);
        $this->assertGuest();
    }

    public function test_registration_fails_with_weak_or_unconfirmed_password(): void
    {
        // Password tidak cocok dengan konfirmasi
        $response = $this->post(route('register.store'), [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors(['password' => 'Konfirmasi password tidak cocok.']);
        $this->assertGuest();

        // Password terlalu pendek (< 8 karakter)
        $responseWeak = $this->post(route('register.store'), [
            'name' => 'Budi',
            'email' => 'budi2@example.com',
            'password' => '123',
            'password_confirmation' => '123',
        ]);

        $responseWeak->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    public function test_authenticated_user_cannot_access_registration(): void
    {
        $user = User::factory()->create([
            'role' => 'pelanggan',
        ]);

        $response = $this->actingAs($user)->get(route('register'));
        $response->assertRedirect(route('pelanggan.dashboard'));

        $responseStore = $this->actingAs($user)->post(route('register.store'), [
            'name' => 'Another User',
            'email' => 'another@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $responseStore->assertRedirect(route('pelanggan.dashboard'));
    }

    public function test_registration_is_throttled_after_five_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('register.store'), [
                'name' => "User {$i}",
                'email' => "invalid-email-{$i}",
                'password' => 'pwd',
                'password_confirmation' => 'pwd',
            ]);
        }

        // Percobaan ke-6 harus ditolak oleh rate limiter (HTTP 429 Too Many Requests)
        $response = $this->post(route('register.store'), [
            'name' => 'User 6',
            'email' => 'user6@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(429);
    }
}
