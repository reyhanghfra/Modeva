@extends('layouts.customer')

@section('title', 'Pembayaran Pesanan ' . $order->order_number . ' - Modeva')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 space-y-8">
    {{-- Header Halaman --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-[#EFECE6]">
        <div>
            <div class="flex items-center gap-2 text-xs text-[#78716C] mb-1">
                <span>Pesanan</span>
                <x-icon name="chevron-right" class="w-3.5 h-3.5" />
                <span class="font-mono text-[#1C1917] font-medium">{{ $order->order_number }}</span>
            </div>
            <h1 class="font-serif text-3xl sm:text-4xl font-semibold text-[#1C1917]">
                Instruksi Pembayaran Transfer
            </h1>
        </div>
        <div>
            <x-admin.status-badge
                :variant="$payment->status_variant"
                :label="$payment->status_label"
            />
        </div>
    </div>

    {{-- Notifikasi Flash --}}
    @if (session('status'))
        <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-900 flex items-start gap-3">
            <x-icon name="check-circle" class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" strokeWidth="2" />
            <div class="font-medium">{{ session('status') }}</div>
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-900 flex items-start gap-3">
            <x-icon name="alert-triangle" class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" strokeWidth="2" />
            <div class="font-medium">{{ session('error') }}</div>
        </div>
    @endif

    {{-- Banner Status Khusus: Ditolak / Menunggu Verifikasi / Terverifikasi --}}
    @if ($payment->status === \App\Models\Payment::STATUS_DITOLAK)
        <div class="rounded-3xl bg-rose-50 border border-rose-200 p-6 sm:p-7 space-y-3">
            <div class="flex items-center gap-2 text-rose-900 font-semibold text-base">
                <x-icon name="x-circle" class="w-5 h-5 text-rose-600" strokeWidth="2" />
                <span>Bukti Pembayaran Ditolak</span>
            </div>
            <p class="text-xs sm:text-sm text-rose-800 leading-relaxed">
                <span class="font-semibold">Alasan penolakan dari admin:</span>
                "{{ $payment->rejection_reason ?? 'Berkas bukti transfer tidak sesuai atau tidak terbaca.' }}"
            </p>
            <div class="pt-2">
                <a
                    href="{{ route('pelanggan.payments.upload', $order->order_number) }}"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-rose-700 hover:bg-rose-800 text-white text-xs sm:text-sm font-medium transition-all shadow-xs"
                >
                    <x-icon name="upload" class="w-4 h-4" />
                    <span>Unggah Ulang Bukti Pembayaran</span>
                </a>
            </div>
        </div>
    @elseif ($payment->status === \App\Models\Payment::STATUS_MENUNGGU_VERIFIKASI)
        <div class="rounded-3xl bg-amber-50/70 border border-amber-200/80 p-6 sm:p-7 space-y-4">
            <div class="flex items-center gap-2 text-amber-900 font-semibold text-base">
                <x-icon name="clock" class="w-5 h-5 text-amber-700" strokeWidth="2" />
                <span>Bukti Diterima, Menunggu Verifikasi</span>
            </div>
            <p class="text-xs sm:text-sm text-amber-800 leading-relaxed">
                Bukti transfer Anda telah tersimpan dan sedang dalam antrean verifikasi oleh tim admin Modeva.
                Waktu unggah: <span class="font-medium">{{ $payment->uploaded_at?->format('d M Y, H:i') ?? '-' }} WIB</span>.
            </p>
            @if ($payment->proof_path)
                <div class="pt-2 flex items-center gap-4">
                    <a
                        href="{{ route('pelanggan.payments.proof', [$order->order_number, $payment]) }}"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-amber-300 rounded-full text-xs font-medium text-amber-900 hover:bg-amber-100/50 transition-colors"
                    >
                        <x-icon name="eye" class="w-4 h-4 text-amber-700" />
                        <span>Lihat Bukti yang Telah Diunggah</span>
                    </a>
                </div>
            @endif
        </div>
    @elseif ($payment->status === \App\Models\Payment::STATUS_TERVERIFIKASI)
        <div class="rounded-3xl bg-emerald-50/70 border border-emerald-200/80 p-6 sm:p-7 space-y-2">
            <div class="flex items-center gap-2 text-emerald-900 font-semibold text-base">
                <x-icon name="check-circle" class="w-5 h-5 text-emerald-700" strokeWidth="2" />
                <span>Pembayaran Berhasil Terverifikasi</span>
            </div>
            <p class="text-xs sm:text-sm text-emerald-800 leading-relaxed">
                Terima kasih! Pembayaran Anda telah dikonfirmasi oleh tim admin Modeva pada
                <span class="font-medium">{{ $payment->verified_at?->format('d M Y, H:i') ?? '-' }} WIB</span>.
                Pesanan Anda kini sedang diproses untuk pengemasan dan pengiriman.
            </p>
        </div>
    @endif

    {{-- Card Total Transfer & Batas Waktu --}}
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-[#F3EFEA]">
            <div>
                <span class="text-xs font-medium uppercase tracking-[0.15em] text-[#78716C]">Total Tagihan Transfer</span>
                <div class="font-serif text-3xl sm:text-4xl lg:text-5xl font-bold text-[#1C1917] mt-1">
                    {{ \App\Support\Rupiah::format($payment->amount) }}
                </div>
                <p class="text-xs text-[#A8A29E] mt-1">
                    Mohon transfer tepat hingga digit terakhir untuk mempercepat proses pencocokan otomatis.
                </p>
            </div>
            <div class="sm:text-right bg-[#FAF8F5] sm:bg-transparent p-4 sm:p-0 rounded-2xl sm:rounded-none">
                <span class="text-xs text-[#78716C] block">Batas Waktu Pembayaran</span>
                <div class="flex sm:justify-end items-center gap-1.5 mt-1 text-sm font-semibold text-[#1C1917]">
                    <x-icon name="clock" class="w-4 h-4 text-amber-600" />
                    <span>{{ $deadline->format('d M Y, H:i') }} WIB</span>
                </div>
                <span class="text-[11px] text-[#A8A29E] block mt-0.5">(24 jam sejak pesanan dibuat)</span>
            </div>
        </div>

        {{-- Daftar Rekening Bank Tujuan --}}
        <div class="space-y-4">
            <h2 class="font-serif text-xl font-semibold text-[#1C1917]">
                Pilihan Rekening Bank Tujuan
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($banks as $bank)
                    <div class="bg-[#FAF8F5] border border-[#EFECE6] rounded-2xl p-5 flex flex-col justify-between gap-3 hover:border-[#D0C5B8] transition-colors">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-sm text-[#1C1917] tracking-wider">{{ $bank['bank_name'] }}</span>
                            <span class="text-[11px] uppercase tracking-wider text-[#78716C] bg-white px-2.5 py-1 rounded-full border border-[#EFECE6]">
                                Transfer Bank
                            </span>
                        </div>

                        <div>
                            <div class="text-xs text-[#78716C]">Nomor Rekening:</div>
                            <div class="flex items-center justify-between mt-1">
                                <span class="font-mono text-base sm:text-lg font-bold text-[#1C1917] tracking-wider" id="acc-{{ $bank['code'] }}">
                                    {{ $bank['account_number'] }}
                                </span>
                                <button
                                    type="button"
                                    onclick="copyToClipboard('{{ $bank['account_number'] }}', this)"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-[#1C1917] bg-white hover:bg-[#EDE7DF] border border-[#D0C5B8] rounded-full transition-all cursor-pointer"
                                    title="Salin nomor rekening"
                                >
                                    <x-icon name="copy" class="w-3.5 h-3.5" />
                                    <span class="btn-copy-text">Salin</span>
                                </button>
                            </div>
                            <div class="text-xs text-[#78716C] mt-2">
                                a.n. <span class="font-medium text-[#1C1917]">{{ $bank['account_name'] }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Tombol Aksi Utama --}}
        @if ($payment->status === \App\Models\Payment::STATUS_BELUM_DIBAYAR)
            <div class="pt-4 border-t border-[#F3EFEA] flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-[#78716C]">
                    Sudah melakukan transfer melalui ATM, m-Banking, atau Internet Banking?
                </div>
                <a
                    href="{{ route('pelanggan.payments.upload', $order->order_number) }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-[#1C1917] hover:bg-black text-white text-sm font-medium rounded-full shadow-xs transition-all"
                >
                    <x-icon name="upload" class="w-4 h-4" />
                    <span>Saya Sudah Transfer, Upload Bukti</span>
                </a>
            </div>
        @endif
    </div>

    {{-- Panduan Langkah Transfer Singkat --}}
    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-4">
        <h2 class="font-serif text-lg font-semibold text-[#1C1917]">
            Langkah Pembayaran Singkat
        </h2>
        <ol class="space-y-3 text-xs sm:text-sm text-[#78716C]">
            <li class="flex items-start gap-3">
                <span class="flex items-center justify-center w-6 h-6 rounded-full bg-[#EDE7DF] text-[#1C1917] font-semibold text-xs shrink-0">1</span>
                <span>Pilih salah satu bank di atas dan transfer nominal sebesar <strong>{{ \App\Support\Rupiah::format($payment->amount) }}</strong> ke rekening resmi Modeva.</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="flex items-center justify-center w-6 h-6 rounded-full bg-[#EDE7DF] text-[#1C1917] font-semibold text-xs shrink-0">2</span>
                <span>Simpan foto struk transfer atau tangkapan layar (screenshot) mutasi transfer yang jelas dan memuat nomor referensi bank.</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="flex items-center justify-center w-6 h-6 rounded-full bg-[#EDE7DF] text-[#1C1917] font-semibold text-xs shrink-0">3</span>
                <span>Klik tombol <strong>"Upload Bukti Pembayaran"</strong> dan masukkan nama rekening pengirim beserta berkas foto bukti transfer.</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="flex items-center justify-center w-6 h-6 rounded-full bg-[#EDE7DF] text-[#1C1917] font-semibold text-xs shrink-0">4</span>
                <span>Tim admin kami akan memverifikasi pembayaran Anda dalam kurun waktu 1x24 jam kerja.</span>
            </li>
        </ol>
    </div>

    {{-- Ringkasan Pesanan --}}
    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-4">
        <h2 class="font-serif text-lg font-semibold text-[#1C1917]">
            Ringkasan Pesanan
        </h2>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
            <div>
                <dt class="text-[#78716C]">Nomor Pesanan</dt>
                <dd class="font-mono font-medium text-[#1C1917] mt-0.5">{{ $order->order_number }}</dd>
            </div>
            <div>
                <dt class="text-[#78716C]">Waktu Pemesanan</dt>
                <dd class="font-medium text-[#1C1917] mt-0.5">
                    {{ $order->created_at ? $order->created_at->format('d M Y, H:i') : '-' }} WIB
                </dd>
            </div>
            <div>
                <dt class="text-[#78716C]">Status Pesanan</dt>
                <dd class="font-medium text-[#1C1917] mt-0.5">
                    {{ $order->status_label ?? ucfirst(str_replace('_', ' ', $order->status)) }}
                </dd>
            </div>
            @if ($order->shipping_address)
                <div class="sm:col-span-2">
                    <dt class="text-[#78716C]">Alamat Pengiriman</dt>
                    <dd class="font-medium text-[#1C1917] mt-0.5 whitespace-pre-line leading-relaxed">
                        {{ $order->shipping_address }}
                    </dd>
                </div>
            @endif
        </dl>
    </div>
</div>

@push('scripts')
<script>
    function copyToClipboard(text, buttonElement) {
        if (!navigator.clipboard) {
            // Fallback execCommand jika clipboard API tidak tersedia
            const textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showCopyFeedback(buttonElement);
            return;
        }

        navigator.clipboard.writeText(text).then(function() {
            showCopyFeedback(buttonElement);
        }).catch(function(err) {
            console.error('Gagal menyalin:', err);
        });
    }

    function showCopyFeedback(btn) {
        const textSpan = btn.querySelector('.btn-copy-text');
        const originalText = textSpan ? textSpan.textContent : 'Salin';
        if (textSpan) {
            textSpan.textContent = 'Tersalin!';
        }
        btn.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-300');

        setTimeout(function() {
            if (textSpan) {
                textSpan.textContent = originalText;
            }
            btn.classList.remove('bg-emerald-50', 'text-emerald-700', 'border-emerald-300');
        }, 2000);
    }
</script>
@endpush
@endsection
