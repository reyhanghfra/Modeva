<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Modeva');
        $response->assertSee('Masuk');
    }

    public function test_admin_can_login_and_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@modeva.test',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'admin@modeva.test',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_petugas_can_login_and_is_redirected_to_petugas_dashboard(): void
    {
        $petugas = User::factory()->create([
            'email' => 'petugas@modeva.test',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'petugas@modeva.test',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($petugas);
        $response->assertRedirect(route('petugas.dashboard'));
    }

    public function test_pelanggan_can_login_and_is_redirected_to_home(): void
    {
        $pelanggan = User::factory()->create([
            'email' => 'pelanggan@modeva.test',
            'role' => 'pelanggan',
            'is_active' => true,
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'pelanggan@modeva.test',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($pelanggan);
        $response->assertRedirect(route('pelanggan.dashboard'));
    }

    public function test_login_fails_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'admin@modeva.test',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'admin@modeva.test',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['email' => 'Email atau password salah.']);
    }

    public function test_login_fails_with_unregistered_email(): void
    {
        $response = $this->post(route('login.store'), [
            'email' => 'unknown@modeva.test',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['email' => 'Email atau password salah.']);
    }

    public function test_login_fails_with_inactive_account(): void
    {
        User::factory()->create([
            'email' => 'nonaktif@modeva.test',
            'role' => 'pelanggan',
            'is_active' => false,
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'nonaktif@modeva.test',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'email' => 'Akun Anda dinonaktifkan, hubungi admin.',
        ]);
    }

    public function test_login_is_rate_limited_after_five_failed_attempts(): void
    {
        RateLimiter::clear('spammer@modeva.test|127.0.0.1');

        User::factory()->create([
            'email' => 'spammer@modeva.test',
            'password' => 'password',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('login.store'), [
                'email' => 'spammer@modeva.test',
                'password' => 'wrong-password',
            ]);
            $response->assertSessionHasErrors(['email' => 'Email atau password salah.']);
        }

        // Percobaan ke-6 harus diblokir oleh rate limiter
        $blockedResponse = $this->post(route('login.store'), [
            'email' => 'spammer@modeva.test',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $blockedResponse->assertSessionHasErrors('email');
        $errorMessage = session('errors')->first('email');
        $this->assertStringContainsString('Terlalu banyak percobaan', $errorMessage);
        $this->assertStringContainsString('detik', $errorMessage);
    }

    public function test_guest_visiting_admin_dashboard_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_visiting_petugas_dashboard_is_redirected_to_login(): void
    {
        $response = $this->get(route('petugas.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_pelanggan_visiting_admin_dashboard_gets_403(): void
    {
        $pelanggan = User::factory()->create([
            'role' => 'pelanggan',
            'is_active' => true,
        ]);

        $response = $this->actingAs($pelanggan)->get(route('admin.dashboard'));

        $response->assertStatus(403);
        $response->assertSee('Akses Ditolak');
        $response->assertSee(route('home'));
    }

    public function test_petugas_visiting_admin_dashboard_gets_403(): void
    {
        $petugas = User::factory()->create([
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $response = $this->actingAs($petugas)->get(route('admin.dashboard'));

        $response->assertStatus(403);
        $response->assertSee('Akses Ditolak');
        $response->assertSee(route('petugas.dashboard'));
    }

    public function test_pelanggan_visiting_petugas_dashboard_gets_403(): void
    {
        $pelanggan = User::factory()->create([
            'role' => 'pelanggan',
            'is_active' => true,
        ]);

        $response = $this->actingAs($pelanggan)->get(route('petugas.dashboard'));

        $response->assertStatus(403);
        $response->assertSee('Akses Ditolak');
        $response->assertSee(route('home'));
    }

    public function test_authenticated_admin_visiting_login_page_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('login'));

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_authenticated_petugas_visiting_login_page_is_redirected_to_petugas_dashboard(): void
    {
        $petugas = User::factory()->create([
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $response = $this->actingAs($petugas)->get(route('login'));

        $response->assertRedirect(route('petugas.dashboard'));
    }

    public function test_authenticated_pelanggan_visiting_login_page_is_redirected_to_home(): void
    {
        $pelanggan = User::factory()->create([
            'role' => 'pelanggan',
            'is_active' => true,
        ]);

        $response = $this->actingAs($pelanggan)->get(route('login'));

        $response->assertRedirect(route('pelanggan.dashboard'));
    }

    public function test_inactive_user_accessing_protected_route_is_logged_out_and_redirected(): void
    {
        $inactiveUser = User::factory()->create([
            'role' => 'admin',
            'is_active' => false,
        ]);

        $response = $this->actingAs($inactiveUser)->get(route('admin.dashboard'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors([
            'email' => 'Akun Anda dinonaktifkan, hubungi admin.',
        ]);
    }

    public function test_logout_removes_session(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }

    public function test_user_can_login_with_remember_me(): void
    {
        $user = User::factory()->create([
            'email' => 'remember@modeva.test',
            'password' => 'password',
            'role' => 'pelanggan',
            'is_active' => true,
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'remember@modeva.test',
            'password' => 'password',
            'remember' => '1',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('pelanggan.dashboard'));
        $response->assertCookie(Auth::guard()->getRecallerName());
    }

    public function test_pelanggan_is_redirected_to_intended_url_after_login(): void
    {
        $pelanggan = User::factory()->create([
            'email' => 'pelanggan@modeva.test',
            'role' => 'pelanggan',
            'is_active' => true,
        ]);

        session()->put('url.intended', 'http://localhost/pesanan/MDV-999/pembayaran');

        $response = $this->post(route('login.store'), [
            'email' => 'pelanggan@modeva.test',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($pelanggan);
        $response->assertRedirect('http://localhost/pesanan/MDV-999/pembayaran');
    }

    public function test_admin_and_petugas_ignore_intended_url_and_always_redirect_to_dashboard(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@modeva.test',
            'role' => 'admin',
            'is_active' => true,
        ]);

        session()->put('url.intended', 'http://localhost/pesanan/MDV-999/pembayaran');

        $responseAdmin = $this->post(route('login.store'), [
            'email' => 'admin@modeva.test',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $responseAdmin->assertRedirect(route('admin.dashboard'));

        Auth::logout();

        $petugas = User::factory()->create([
            'email' => 'petugas@modeva.test',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        session()->put('url.intended', 'http://localhost/pesanan/MDV-999/pembayaran');

        $responsePetugas = $this->post(route('login.store'), [
            'email' => 'petugas@modeva.test',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($petugas);
        $responsePetugas->assertRedirect(route('petugas.dashboard'));
    }

    public function test_pelanggan_cannot_access_admin_dashboard_gets_403(): void
    {
        $pelanggan = User::factory()->create([
            'role' => 'pelanggan',
            'is_active' => true,
        ]);

        $response = $this->actingAs($pelanggan)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }
}
