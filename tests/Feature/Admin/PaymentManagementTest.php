<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $petugas;
    protected User $pelanggan;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat tabel orders sementara jika belum ada di migrasi
        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function ($table) {
                $table->id();
                $table->string('order_number');
                $table->foreignId('user_id');
                $table->unsignedBigInteger('total');
                $table->string('status')->default('verifikasi_pembayaran');
                $table->timestamps();
            });
        }

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->petugas = User::factory()->create([
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $this->pelanggan = User::factory()->create([
            'role' => 'pelanggan',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_payment_index_with_search_and_filters(): void
    {
        $order = Order::create([
            'order_number' => 'MDV-250611',
            'user_id' => $this->pelanggan->id,
            'total' => 500000,
            'status' => 'verifikasi_pembayaran',
        ]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'bank_name' => 'BCA',
            'sender_name' => 'Anindya Putri',
            'status' => Payment::STATUS_MENUNGGU_VERIFIKASI,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.payments.index', [
            'search' => 'Anindya',
            'status' => 'menunggu_verifikasi',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Verifikasi Pembayaran');
        $response->assertSee('MDV-250611');
        $response->assertSee('Anindya Putri');
    }

    public function test_admin_can_view_payment_detail(): void
    {
        $order = Order::create([
            'order_number' => 'MDV-100001',
            'user_id' => $this->pelanggan->id,
            'total' => 750000,
            'status' => 'verifikasi_pembayaran',
        ]);

        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'amount' => 750000,
            'status' => Payment::STATUS_MENUNGGU_VERIFIKASI,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.payments.show', $payment));

        $response->assertStatus(200);
        $response->assertSee('Tinjau Pembayaran');
        $response->assertSee('MDV-100001');
        $response->assertSee('Verifikasi Pembayaran');
    }

    public function test_verification_changes_payment_and_order_status_and_records_verifier(): void
    {
        $order = Order::create([
            'order_number' => 'MDV-100002',
            'user_id' => $this->pelanggan->id,
            'total' => 600000,
            'status' => 'verifikasi_pembayaran',
        ]);

        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'status' => Payment::STATUS_MENUNGGU_VERIFIKASI,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.payments.verify', $payment));

        $response->assertRedirect(route('admin.payments.show', $payment));
        $response->assertSessionHas('success');

        $payment->refresh();
        $order->refresh();

        $this->assertSame(Payment::STATUS_TERVERIFIKASI, $payment->status);
        $this->assertSame($this->admin->id, $payment->verified_by);
        $this->assertNotNull($payment->verified_at);
        $this->assertSame('diproses', $order->status);
    }

    public function test_cannot_verify_payment_twice(): void
    {
        $payment = Payment::factory()->create([
            'status' => Payment::STATUS_TERVERIFIKASI,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.payments.verify', $payment));

        $response->assertSessionHas('error');
    }

    public function test_rejection_changes_payment_and_order_status_with_reason(): void
    {
        $order = Order::create([
            'order_number' => 'MDV-100003',
            'user_id' => $this->pelanggan->id,
            'total' => 450000,
            'status' => 'verifikasi_pembayaran',
        ]);

        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'status' => Payment::STATUS_MENUNGGU_VERIFIKASI,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.payments.reject', $payment), [
            'rejection_reason' => 'Nominal transfer tidak sesuai dengan invoice.',
        ]);

        $response->assertRedirect(route('admin.payments.show', $payment));
        $response->assertSessionHas('success');

        $payment->refresh();
        $order->refresh();

        $this->assertSame(Payment::STATUS_DITOLAK, $payment->status);
        $this->assertSame('Nominal transfer tidak sesuai dengan invoice.', $payment->rejection_reason);
        $this->assertSame('menunggu_pembayaran', $order->status);
    }

    public function test_rejection_without_reason_fails(): void
    {
        $payment = Payment::factory()->create([
            'status' => Payment::STATUS_MENUNGGU_VERIFIKASI,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.payments.reject', $payment), [
            'rejection_reason' => '123', // kurang dari 5 karakter
        ]);

        $response->assertSessionHasErrors('rejection_reason');
    }

    public function test_only_admin_and_petugas_can_access_proof(): void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('struk.jpg');
        $path = $file->store('payment-proofs', 'local');

        $payment = Payment::factory()->create([
            'proof_path' => $path,
        ]);

        // Admin diizinkan
        $responseAdmin = $this->actingAs($this->admin)->get(route('admin.payments.proof', $payment));
        $responseAdmin->assertStatus(200);

        // Petugas diizinkan
        $responsePetugas = $this->actingAs($this->petugas)->get(route('admin.payments.proof', $payment));
        $responsePetugas->assertStatus(200);

        // Pelanggan ditolak (403)
        $responsePelanggan = $this->actingAs($this->pelanggan)->get(route('admin.payments.proof', $payment));
        $responsePelanggan->assertStatus(403);
    }

    public function test_pelanggan_gets_403_on_admin_payments(): void
    {
        $response = $this->actingAs($this->pelanggan)->get(route('admin.payments.index'));
        $response->assertStatus(403);
    }

    public function test_payment_renders_gracefully_when_orders_table_does_not_exist(): void
    {
        Schema::dropIfExists('orders');

        $payment = Payment::factory()->create([
            'order_id' => 999,
            'status' => Payment::STATUS_MENUNGGU_VERIFIKASI,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.payments.index'));
        $response->assertOk();
        $response->assertSee('MDV-000999');

        $responseShow = $this->actingAs($this->admin)->get(route('admin.payments.show', $payment));
        $responseShow->assertOk();
        $responseShow->assertSee('MDV-000999');
    }
}
