<?php

namespace Tests\Feature\Pelanggan;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentTransferTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $otherCustomer;
    protected User $admin;
    protected User $petugas;
    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->owner = User::factory()->create([
            'name' => 'Budi Pemilik',
            'email' => 'owner@example.com',
            'role' => 'pelanggan',
            'is_active' => true,
        ]);

        $this->otherCustomer = User::factory()->create([
            'name' => 'Siti Lain',
            'email' => 'other@example.com',
            'role' => 'pelanggan',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'email' => 'admin@modeva.test',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->petugas = User::factory()->create([
            'email' => 'petugas@modeva.test',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $this->order = Order::factory()->create([
            'order_number' => 'MDV-100200',
            'user_id' => $this->owner->id,
            'total' => 500000,
            'status' => Order::STATUS_MENUNGGU_PEMBAYARAN,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Tests: Halaman Pembayaran Transfer (Show)
    |--------------------------------------------------------------------------
    */

    public function test_order_owner_can_view_payment_transfer_page(): void
    {
        $response = $this->actingAs($this->owner)->get(route('pelanggan.payments.show', $this->order->order_number));

        $response->assertStatus(200);
        $response->assertSee('MDV-100200');
        $response->assertSee('Rp500.000');
        $response->assertSee('Instruksi Pembayaran Transfer');
        $response->assertSee('PT Modeva Fashion Indonesia');
        $response->assertSee('Saya Sudah Transfer, Upload Bukti');
    }

    public function test_payment_record_is_created_automatically_once(): void
    {
        $this->assertDatabaseMissing('payments', [
            'order_id' => $this->order->id,
        ]);

        // Kunjungan pertama membuat record Payment
        $this->actingAs($this->owner)->get(route('pelanggan.payments.show', $this->order->order_number));

        $this->assertDatabaseHas('payments', [
            'order_id' => $this->order->id,
            'amount' => 500000,
            'status' => Payment::STATUS_BELUM_DIBAYAR,
        ]);

        $this->assertSame(1, Payment::where('order_id', $this->order->id)->count());

        // Kunjungan kedua tidak menduplikasi record Payment
        $this->actingAs($this->owner)->get(route('pelanggan.payments.show', $this->order->order_number));

        $this->assertSame(1, Payment::where('order_id', $this->order->id)->count());
    }

    public function test_other_customer_cannot_view_payment_page_gets_404(): void
    {
        $response = $this->actingAs($this->otherCustomer)->get(route('pelanggan.payments.show', $this->order->order_number));

        // Route model binding terisolasi ke milik user login => 404
        $response->assertStatus(404);
    }

    public function test_admin_and_petugas_cannot_access_customer_payment_page_gets_403(): void
    {
        $responseAdmin = $this->actingAs($this->admin)->get(route('pelanggan.payments.show', $this->order->order_number));
        $responseAdmin->assertStatus(403);

        $responsePetugas = $this->actingAs($this->petugas)->get(route('pelanggan.payments.show', $this->order->order_number));
        $responsePetugas->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('pelanggan.payments.show', $this->order->order_number));

        $response->assertRedirect(route('login'));
    }

    /*
    |--------------------------------------------------------------------------
    | Tests: Unggah Bukti Pembayaran (Upload & Store)
    |--------------------------------------------------------------------------
    */

    public function test_customer_can_upload_payment_proof_successfully(): void
    {
        $file = UploadedFile::fake()->image('bukti_transfer.jpg', 600, 800)->size(500);

        $response = $this->actingAs($this->owner)->post(route('pelanggan.payments.store', $this->order->order_number), [
            'bank_name' => 'BCA',
            'sender_name' => 'Budi Santoso',
            'proof' => $file,
        ]);

        $response->assertRedirect(route('pelanggan.payments.show', $this->order->order_number));
        $response->assertSessionHas('status');

        // Pastikan payment terupdate
        $payment = Payment::where('order_id', $this->order->id)->first();
        $this->assertNotNull($payment);
        $this->assertSame(Payment::STATUS_MENUNGGU_VERIFIKASI, $payment->status);
        $this->assertSame('BCA', $payment->bank_name);
        $this->assertSame('Budi Santoso', $payment->sender_name);
        $this->assertNotNull($payment->proof_path);

        // Pastikan file tersimpan di disk privat
        Storage::disk('local')->assertExists($payment->proof_path);

        // Pastikan status order berubah menjadi verifikasi_pembayaran
        $this->assertSame(Order::STATUS_VERIFIKASI_PEMBAYARAN, $this->order->fresh()->status);
    }

    public function test_upload_fails_when_file_is_not_an_image(): void
    {
        $file = UploadedFile::fake()->create('dokumen.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->owner)->post(route('pelanggan.payments.store', $this->order->order_number), [
            'bank_name' => 'BCA',
            'sender_name' => 'Budi Santoso',
            'proof' => $file,
        ]);

        $response->assertSessionHasErrors(['proof' => 'Berkas bukti transfer harus berupa gambar.']);
    }

    public function test_upload_fails_when_file_exceeds_max_size(): void
    {
        // File > 2048 KB (misal 3000 KB)
        $file = UploadedFile::fake()->image('struk_besar.png')->size(3000);

        $response = $this->actingAs($this->owner)->post(route('pelanggan.payments.store', $this->order->order_number), [
            'bank_name' => 'BCA',
            'sender_name' => 'Budi Santoso',
            'proof' => $file,
        ]);

        $response->assertSessionHasErrors(['proof' => 'Ukuran berkas bukti transfer tidak boleh melebihi 2MB.']);
    }

    public function test_upload_fails_with_invalid_bank_name_or_empty_fields(): void
    {
        $file = UploadedFile::fake()->image('bukti.jpg');

        $response = $this->actingAs($this->owner)->post(route('pelanggan.payments.store', $this->order->order_number), [
            'bank_name' => 'Bank Antah Berantah',
            'sender_name' => '',
            'proof' => $file,
        ]);

        $response->assertSessionHasErrors(['bank_name', 'sender_name']);
    }

    public function test_upload_rejected_when_payment_is_already_pending_or_verified(): void
    {
        $file = UploadedFile::fake()->image('bukti.jpg');

        // Status sudah menunggu_verifikasi
        Payment::create([
            'order_id' => $this->order->id,
            'method' => 'transfer',
            'bank_name' => 'BCA',
            'sender_name' => 'Budi',
            'amount' => 500000,
            'status' => Payment::STATUS_MENUNGGU_VERIFIKASI,
            'proof_path' => 'payment-proofs/1/sample.jpg',
        ]);

        $response = $this->actingAs($this->owner)->post(route('pelanggan.payments.store', $this->order->order_number), [
            'bank_name' => 'BCA',
            'sender_name' => 'Budi',
            'proof' => $file,
        ]);

        $response->assertRedirect(route('pelanggan.payments.show', $this->order->order_number));
        $response->assertSessionHas('error');
    }

    public function test_reupload_after_rejection_clears_reason_and_deletes_old_file(): void
    {
        // Siapkan berkas bukti lama di storage
        $oldFilePath = 'payment-proofs/' . $this->order->id . '/old_proof.jpg';
        Storage::disk('local')->put($oldFilePath, 'isi berkas lama');
        Storage::disk('local')->assertExists($oldFilePath);

        $payment = Payment::create([
            'order_id' => $this->order->id,
            'method' => 'transfer',
            'bank_name' => 'BCA',
            'sender_name' => 'Budi',
            'amount' => 500000,
            'status' => Payment::STATUS_DITOLAK,
            'rejection_reason' => 'Nominal transfer salah.',
            'proof_path' => $oldFilePath,
        ]);

        $newFile = UploadedFile::fake()->image('bukti_baru.jpg', 600, 800);

        $response = $this->actingAs($this->owner)->post(route('pelanggan.payments.store', $this->order->order_number), [
            'bank_name' => 'Bank Mandiri',
            'sender_name' => 'Budi Santoso Baru',
            'proof' => $newFile,
        ]);

        $response->assertRedirect(route('pelanggan.payments.show', $this->order->order_number));

        $payment->refresh();
        $this->assertSame(Payment::STATUS_MENUNGGU_VERIFIKASI, $payment->status);
        $this->assertNull($payment->rejection_reason);
        $this->assertNotSame($oldFilePath, $payment->proof_path);

        // Berkas lama harus sudah terhapus
        Storage::disk('local')->assertMissing($oldFilePath);

        // Berkas baru harus tersimpan
        Storage::disk('local')->assertExists($payment->proof_path);
    }

    /*
    |--------------------------------------------------------------------------
    | Tests: Akses Berkas Bukti Transfer (Proof Streaming)
    |--------------------------------------------------------------------------
    */

    public function test_order_owner_can_view_proof_file(): void
    {
        $proofPath = 'payment-proofs/' . $this->order->id . '/test_proof.jpg';
        Storage::disk('local')->put($proofPath, 'dummy-image-content');

        $payment = Payment::create([
            'order_id' => $this->order->id,
            'method' => 'transfer',
            'bank_name' => 'BCA',
            'sender_name' => 'Budi',
            'amount' => 500000,
            'status' => Payment::STATUS_MENUNGGU_VERIFIKASI,
            'proof_path' => $proofPath,
        ]);

        $response = $this->actingAs($this->owner)->get(
            route('pelanggan.payments.proof', [$this->order->order_number, $payment])
        );

        $response->assertStatus(200);
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_other_customer_cannot_view_proof_file_gets_403(): void
    {
        $proofPath = 'payment-proofs/' . $this->order->id . '/test_proof.jpg';
        Storage::disk('local')->put($proofPath, 'dummy-image-content');

        $payment = Payment::create([
            'order_id' => $this->order->id,
            'method' => 'transfer',
            'bank_name' => 'BCA',
            'sender_name' => 'Budi',
            'amount' => 500000,
            'status' => Payment::STATUS_MENUNGGU_VERIFIKASI,
            'proof_path' => $proofPath,
        ]);

        // Pelanggan lain tidak diizinkan
        $response = $this->actingAs($this->otherCustomer)->get(
            route('pelanggan.payments.proof', [$this->order->order_number, $payment])
        );

        $response->assertStatus(403);
    }

    public function test_admin_and_petugas_can_view_proof_file_via_pelanggan_route(): void
    {
        $proofPath = 'payment-proofs/' . $this->order->id . '/test_proof.jpg';
        Storage::disk('local')->put($proofPath, 'dummy-image-content');

        $payment = Payment::create([
            'order_id' => $this->order->id,
            'method' => 'transfer',
            'bank_name' => 'BCA',
            'sender_name' => 'Budi',
            'amount' => 500000,
            'status' => Payment::STATUS_MENUNGGU_VERIFIKASI,
            'proof_path' => $proofPath,
        ]);

        $responseAdmin = $this->actingAs($this->admin)->get(
            route('pelanggan.payments.proof', [$this->order->order_number, $payment])
        );
        $responseAdmin->assertStatus(200);

        $responsePetugas = $this->actingAs($this->petugas)->get(
            route('pelanggan.payments.proof', [$this->order->order_number, $payment])
        );
        $responsePetugas->assertStatus(200);
    }
}
