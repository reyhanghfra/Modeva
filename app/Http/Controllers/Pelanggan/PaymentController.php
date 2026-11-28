<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pelanggan\UploadPaymentProofRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentProofService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    /**
     * Menampilkan halaman instruksi pembayaran transfer bank untuk pesanan.
     */
    public function show(Order $order): View
    {
        // Pastikan ada record Payment untuk pesanan (firstOrCreate)
        $payment = Payment::firstOrCreate(
            ['order_id' => $order->id],
            [
                'method' => 'transfer',
                'bank_name' => config('payment.banks.0.bank_name', 'BCA'),
                'sender_name' => $order->user?->name ?? 'Pelanggan Modeva',
                'amount' => $order->total,
                'status' => Payment::STATUS_BELUM_DIBAYAR,
            ]
        );

        $banks = config('payment.banks', []);
        $deadlineHours = config('payment.deadline_hours', 24);
        $deadline = $order->created_at ? $order->created_at->copy()->addHours($deadlineHours) : now()->addHours($deadlineHours);

        return view('pelanggan.payments.show', compact('order', 'payment', 'banks', 'deadline'));
    }

    /**
     * Menampilkan formulir pengunggahan berkas bukti transfer pembayaran.
     */
    public function upload(Order $order): View|RedirectResponse
    {
        $payment = Payment::firstOrCreate(
            ['order_id' => $order->id],
            [
                'method' => 'transfer',
                'bank_name' => config('payment.banks.0.bank_name', 'BCA'),
                'sender_name' => $order->user?->name ?? 'Pelanggan Modeva',
                'amount' => $order->total,
                'status' => Payment::STATUS_BELUM_DIBAYAR,
            ]
        );

        // Jika status tidak memungkinkan upload (misal sudah menunggu verifikasi atau terverifikasi)
        if ($payment->status === Payment::STATUS_MENUNGGU_VERIFIKASI) {
            return redirect()
                ->route('pelanggan.payments.show', $order->order_number)
                ->with('status', 'Bukti pembayaran Anda sudah diterima dan sedang menunggu verifikasi admin.');
        }

        if ($payment->status === Payment::STATUS_TERVERIFIKASI) {
            return redirect()
                ->route('pelanggan.payments.show', $order->order_number)
                ->with('status', 'Pembayaran pesanan ini telah terverifikasi.');
        }

        $banks = config('payment.banks', []);

        return view('pelanggan.payments.upload', compact('order', 'payment', 'banks'));
    }

    /**
     * Memproses penyimpanan berkas bukti transfer pembayaran.
     */
    public function store(
        UploadPaymentProofRequest $request,
        Order $order,
        PaymentProofService $service
    ): RedirectResponse {
        $payment = Payment::firstOrCreate(
            ['order_id' => $order->id],
            [
                'method' => 'transfer',
                'bank_name' => config('payment.banks.0.bank_name', 'BCA'),
                'sender_name' => $order->user?->name ?? 'Pelanggan Modeva',
                'amount' => $order->total,
                'status' => Payment::STATUS_BELUM_DIBAYAR,
            ]
        );

        try {
            $service->submit($order, $payment, $request->validated(), $request->file('proof'));

            return redirect()
                ->route('pelanggan.payments.show', $order->order_number)
                ->with('status', 'Bukti transfer berhasil diunggah. Tim Modeva akan segera memverifikasi transaksi Anda.');
        } catch (InvalidArgumentException $e) {
            return redirect()
                ->route('pelanggan.payments.show', $order->order_number)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Menyajikan pratinjau berkas bukti transfer privat khusus untuk pemilik pesanan atau petugas/admin.
     */
    public function proof(Request $request, Order $order, Payment $payment): StreamedResponse
    {
        // Pastikan payment ini memang milik pesanan terkait
        if ((int) $payment->order_id !== (int) $order->id) {
            abort(404, 'Data pembayaran tidak sesuai dengan pesanan yang diminta.');
        }

        // Otorisasi via PaymentPolicy::viewProof
        Gate::authorize('viewProof', $payment);

        if (! $payment->proof_path || ! Storage::disk('local')->exists($payment->proof_path)) {
            abort(404, 'Berkas bukti transfer tidak ditemukan di penyimpanan server.');
        }

        $mimeType = Storage::disk('local')->mimeType($payment->proof_path) ?: 'application/octet-stream';

        return Storage::disk('local')->response(
            $payment->proof_path,
            basename($payment->proof_path),
            [
                'Content-Type' => $mimeType,
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }
}
