<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    /**
     * Menampilkan daftar verifikasi pembayaran.
     */
    public function index(Request $request): View
    {
        $query = Payment::query();

        // Cek apakah tabel orders ada untuk eager loading
        if (Schema::hasTable('orders')) {
            $query->with(['order.user', 'verifier']);
        } else {
            $query->with('verifier');
        }

        // Pencarian: ID / nomor pesanan, nama pengirim, nama bank
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('bank_name', 'like', "%{$search}%")
                  ->orWhere('sender_name', 'like', "%{$search}%");

                if (Schema::hasTable('orders')) {
                    $q->orWhereHas('order', function ($orderQuery) use ($search) {
                        $orderQuery->where('order_number', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($userQuery) use ($search) {
                                $userQuery->where('name', 'like', "%{$search}%");
                            });
                    });
                }
            });
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Urutkan status 'menunggu_verifikasi' terlebih dahulu, disusul yang terbaru
        $payments = $query
            ->orderByRaw("CASE WHEN status = 'menunggu_verifikasi' THEN 0 ELSE 1 END")
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        if (! Schema::hasTable('orders')) {
            $payments->getCollection()->each(fn ($p) => $p->setRelation('order', null));
        }

        $pendingCount = Payment::where('status', Payment::STATUS_MENUNGGU_VERIFIKASI)->count();
        $totalPayments = Payment::count();

        return view('admin.payments.index', compact('payments', 'pendingCount', 'totalPayments'));
    }

    /**
     * Menampilkan detail pembayaran dan formulir verifikasi/penolakan.
     */
    public function show(Payment $payment): View
    {
        if (Schema::hasTable('orders')) {
            $payment->load(['order.user', 'verifier']);
        } else {
            $payment->load('verifier');
            $payment->setRelation('order', null);
        }

        return view('admin.payments.show', compact('payment'));
    }

    /**
     * Menyajikan pratinjau file bukti transfer dari disk privat lokal.
     */
    public function proof(Request $request, Payment $payment): StreamedResponse
    {
        Gate::authorize('viewProof', $payment);

        if (! $payment->proof_path || ! Storage::disk('local')->exists($payment->proof_path)) {
            abort(404, 'File bukti transfer tidak ditemukan di penyimpanan server.');
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

    /**
     * Melakukan aksi verifikasi pembayaran.
     */
    public function verify(Request $request, Payment $payment, PaymentVerificationService $service): RedirectResponse
    {
        try {
            $service->verify($payment, $request->user());

            return redirect()
                ->route('admin.payments.show', $payment)
                ->with('success', 'Pembayaran berhasil diverifikasi. Pesanan kini berstatus diproses.');
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Melakukan aksi penolakan pembayaran.
     */
    public function reject(Request $request, Payment $payment, PaymentVerificationService $service): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan pembayaran wajib diisi.',
            'rejection_reason.min' => 'Alasan penolakan pembayaran minimal 5 karakter.',
            'rejection_reason.max' => 'Alasan penolakan pembayaran maksimal 500 karakter.',
        ]);

        try {
            $service->reject($payment, $request->user(), $validated['rejection_reason']);

            return redirect()
                ->route('admin.payments.show', $payment)
                ->with('success', 'Pembayaran telah ditolak. Pelanggan dapat mengunggah ulang bukti transfer.');
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
