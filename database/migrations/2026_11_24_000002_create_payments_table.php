<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Referensi ke tabel orders.
            // TODO: Tambahkan foreign key constraint ('order_id')->constrained()->cascadeOnDelete()
            // setelah tabel orders dibuat oleh modul Pesanan (Orang 3).
            $table->unsignedBigInteger('order_id')->index();

            $table->string('method')->default('transfer');
            $table->string('bank_name');
            $table->string('sender_name');
            $table->unsignedBigInteger('amount'); // Nominal transfer dalam rupiah
            $table->string('proof_path')->nullable(); // Disk 'local' privat: payment-proofs/...
            $table->string('status')->default('belum_dibayar')->index(); // belum_dibayar, menunggu_verifikasi, terverifikasi, ditolak
            $table->text('rejection_reason')->nullable();
            $table->timestamp('uploaded_at')->nullable();

            // Verifikasi oleh Admin / Petugas
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
