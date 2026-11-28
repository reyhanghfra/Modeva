<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Throwable;

class PaymentProofService
{
    /**
     * Memproses pengunggahan bukti transfer pembayaran pesanan pelanggan.
     *
     * @param Order $order
     * @param Payment $payment
     * @param array{bank_name: string, sender_name: string} $data
     * @param UploadedFile $proof
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function submit(Order $order, Payment $payment, array $data, UploadedFile $proof): bool
    {
        // Validasi prasyarat status pesanan dan pembayaran
        if ($order->status !== Order::STATUS_MENUNGGU_PEMBAYARAN) {
            throw new InvalidArgumentException(
                'Bukti transfer tidak dapat diunggah karena pesanan tidak berstatus menunggu pembayaran.'
            );
        }

        $allowedPaymentStatuses = [
            Payment::STATUS_BELUM_DIBAYAR,
            Payment::STATUS_DITOLAK,
        ];

        if (! in_array($payment->status, $allowedPaymentStatuses, true)) {
            throw new InvalidArgumentException(
                'Bukti transfer tidak dapat diunggah karena pembayaran sedang diverifikasi atau sudah selesai.'
            );
        }

        // Simpan file bukti baru ke disk privat 'local' dalam direktori payment-proofs/{order_id} dengan nama hash acak
        $folderPath = "payment-proofs/{$order->id}";
        $newFilePath = $proof->store($folderPath, 'local');

        if (! $newFilePath) {
            throw new InvalidArgumentException('Gagal menyimpan berkas bukti transfer ke penyimpanan server.');
        }

        $oldFilePath = null;

        try {
            DB::transaction(function () use ($order, $payment, $data, $newFilePath, &$oldFilePath) {
                // Kunci baris payment dengan lockForUpdate untuk mencegah race condition / upload ganda simultan
                /** @var Payment $lockedPayment */
                $lockedPayment = Payment::where('id', $payment->id)->lockForUpdate()->firstOrFail();

                // Simpan path file lama untuk dihapus setelah transaksi sukses (jika upload ulang setelah ditolak)
                $oldFilePath = $lockedPayment->proof_path;

                // Perbarui data pembayaran
                $lockedPayment->update([
                    'bank_name' => $data['bank_name'],
                    'sender_name' => $data['sender_name'],
                    'proof_path' => $newFilePath,
                    'status' => Payment::STATUS_MENUNGGU_VERIFIKASI,
                    'uploaded_at' => now(),
                    'rejection_reason' => null,
                    'verified_by' => null,
                    'verified_at' => null,
                ]);

                // Perbarui status pesanan menjadi verifikasi_pembayaran
                $order->update([
                    'status' => Order::STATUS_VERIFIKASI_PEMBAYARAN,
                ]);
            });

            // Jika transaksi sukses dan ada berkas lama yang berbeda, hapus berkas lama dari disk privat
            if ($oldFilePath && $oldFilePath !== $newFilePath && Storage::disk('local')->exists($oldFilePath)) {
                Storage::disk('local')->delete($oldFilePath);
            }

            return true;
        } catch (Throwable $e) {
            // Jika transaksi gagal, hapus file baru yang baru saja di-upload agar tidak menjadi sampah penyimpanan
            if ($newFilePath && Storage::disk('local')->exists($newFilePath)) {
                Storage::disk('local')->delete($newFilePath);
            }

            throw $e;
        }
    }
}
