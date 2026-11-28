<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Payment::with(['order.user', 'verifier']);
        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->where(function ($q) use ($search) {
                $q->where('bank_name', 'like', "%{$search}%")
                    ->orWhere('sender_name', 'like', "%{$search}%")
                    ->orWhereHas('order', fn ($o) => $o->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")));
            });
        }
        if ($request->filled('status')) $query->where('status', $request->input('status'));
        $payments = $query->orderByRaw("CASE WHEN status = 'menunggu_verifikasi' THEN 0 ELSE 1 END")
            ->latest('id')->paginate(10)->withQueryString();
        return view('petugas.payments.index', compact('payments'));
    }

    public function show(Payment $payment): View
    {
        $payment->load(['order.user', 'verifier']);
        return view('petugas.payments.show', compact('payment'));
    }

    public function verify(Request $request, Payment $payment, PaymentVerificationService $service): RedirectResponse
    {
        try {
            $service->verify($payment, $request->user());
            return back()->with('success', 'Pembayaran diverifikasi. Pesanan masuk tahap diproses.');
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, Payment $payment, PaymentVerificationService $service): RedirectResponse
    {
        $data = $request->validate(['rejection_reason' => ['required', 'string', 'min:5', 'max:500']]);
        try {
            $service->reject($payment, $request->user(), $data['rejection_reason']);
            return back()->with('success', 'Pembayaran ditolak. Pelanggan dapat mengunggah ulang bukti transfer.');
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
