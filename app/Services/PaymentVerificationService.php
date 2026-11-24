<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class PaymentVerificationService
{
    /**
     * Memverifikasi pembayaran transfer pelanggan.
     *
     * @throws InvalidArgumentException
     */
    public function verify(Payment $payment, User $user): bool
    {
        return DB::transaction(function () use ($payment, $user) {
            // Kunci baris dengan lockForUpdate untuk mencegah race condition / verifikasi ganda
            /** @var Payment $lockedPayment */
            $lockedPayment = Payment::where('id', $payment->id)->lockForUpdate()->firstOrFail();

            if (! $lockedPayment->isPendingVerification()) {
                throw new InvalidArgumentException('Pembayaran tidak dapat diverifikasi karena tidak berstatus menunggu verifikasi.');
            }

            $lockedPayment->update([
                'status' => Payment::STATUS_TERVERIFIKASI,
                'verified_by' => $user->id,
                'verified_at' => now(),
                'rejection_reason' => null,
            ]);

            // Jika relasi/tabel order ada, perbarui status pesanan menjadi 'diproses'
            if ($lockedPayment->order_id && Schema::hasTable('orders')) {
                $order = Order::where('id', $lockedPayment->order_id)->lockForUpdate()->first();
                if ($order) {
                    $order->update(['status' => 'diproses']);
                }
            }

            return true;
        });
    }

    /**
     * Menolak pembayaran transfer pelanggan dengan alasan penolakan.
     *
     * @throws InvalidArgumentException
     */
    public function reject(Payment $payment, User $user, string $reason): bool
    {
        $trimmedReason = trim($reason);
        if (mb_strlen($trimmedReason) < 5) {
            throw new InvalidArgumentException('Alasan penolakan pembayaran minimal harus 5 karakter.');
        }

        return DB::transaction(function () use ($payment, $user, $trimmedReason) {
            /** @var Payment $lockedPayment */
            $lockedPayment = Payment::where('id', $payment->id)->lockForUpdate()->firstOrFail();

            if (! $lockedPayment->isPendingVerification()) {
                throw new InvalidArgumentException('Pembayaran tidak dapat ditolak karena tidak berstatus menunggu verifikasi.');
            }

            $lockedPayment->update([
                'status' => Payment::STATUS_DITOLAK,
                'rejection_reason' => $trimmedReason,
                'verified_by' => $user->id,
                'verified_at' => now(),
            ]);

            // Jika relasi/tabel order ada, kembalikan status pesanan ke 'menunggu_pembayaran' agar pelanggan dapat upload ulang
            if ($lockedPayment->order_id && Schema::hasTable('orders')) {
                $order = Order::where('id', $lockedPayment->order_id)->lockForUpdate()->first();
                if ($order) {
                    $order->update(['status' => 'menunggu_pembayaran']);
                }
            }

            return true;
        });
    }
}
