@extends('layouts.customer')

@section('title', 'Unggah Bukti Pembayaran ' . $order->order_number . ' - Modeva')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 space-y-8">
    {{-- Header Halaman --}}
    <div class="flex items-center justify-between pb-6 border-b border-[#EFECE6]">
        <div>
            <div class="flex items-center gap-2 text-xs text-[#78716C] mb-1">
                <span>Pesanan</span>
                <x-icon name="chevron-right" class="w-3.5 h-3.5" />
                <span class="font-mono text-[#1C1917] font-medium">{{ $order->order_number }}</span>
            </div>
            <h1 class="font-serif text-3xl font-semibold text-[#1C1917]">
                Unggah Bukti Pembayaran
            </h1>
        </div>
        <a
            href="{{ route('pelanggan.payments.show', $order->order_number) }}"
            class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-[#78716C] hover:text-[#1C1917] bg-white border border-[#EFECE6] rounded-full transition-colors"
        >
            <x-icon name="arrow-left" class="w-3.5 h-3.5" />
            <span>Kembali</span>
        </a>
    </div>

    {{-- Notifikasi Error --}}
    @if (session('error'))
        <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-900 flex items-start gap-3">
            <x-icon name="alert-triangle" class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" strokeWidth="2" />
            <div class="font-medium">{{ session('error') }}</div>
        </div>
    @endif

    {{-- Card Formulir Unggah --}}
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#EFECE6] shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
        <form
            method="POST"
            action="{{ route('pelanggan.payments.store', $order->order_number) }}"
            enctype="multipart/form-data"
            id="uploadProofForm"
            class="space-y-6"
        >
            @csrf

            {{-- Nominal Tagihan (Readonly) --}}
            <div>
                <label class="block text-xs font-medium uppercase tracking-[0.1em] text-[#78716C] mb-1.5">
                    Nominal yang Harus Ditransfer
                </label>
                <div class="relative">
                    <input
                        type="text"
                        readonly
                        value="{{ \App\Support\Rupiah::format($payment->amount) }}"
                        class="block w-full rounded-2xl bg-[#FAF8F5] border border-[#EFECE6] px-4 py-3 text-base sm:text-lg font-bold font-mono text-[#1C1917] cursor-not-allowed select-none"
                    >
                </div>
                <p class="text-[11px] text-[#A8A29E] mt-1">
                    Nominal ini ditetapkan sesuai total transaksi pesanan Anda.
                </p>
            </div>

            {{-- Bank Tujuan Transfer --}}
            <div>
                <label for="bank_name" class="block text-xs font-medium uppercase tracking-[0.1em] text-[#78716C] mb-1.5">
                    Bank Tujuan Transfer <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <select
                        id="bank_name"
                        name="bank_name"
                        required
                        class="block w-full appearance-none rounded-2xl border @error('bank_name') border-rose-500 @else border-[#EFECE6] @enderror bg-white px-4 py-3 pr-10 text-sm text-[#1C1917] focus:border-[#1C1917] focus:outline-none focus:ring-1 focus:ring-[#1C1917] transition-all"
                    >
                        <option value="">-- Pilih Rekening Bank Tujuan --</option>
                        @foreach ($banks as $bank)
                            <option
                                value="{{ $bank['bank_name'] }}"
                                {{ old('bank_name', $payment->bank_name) === $bank['bank_name'] ? 'selected' : '' }}
                            >
                                {{ $bank['bank_name'] }} - {{ $bank['account_number'] }} (a.n. {{ $bank['account_name'] }})
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-[#78716C]">
                        <x-icon name="chevron-right" class="w-4 h-4 rotate-90" />
                    </div>
                </div>
                @error('bank_name')
                    <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nama Pemilik Rekening Pengirim --}}
            <div>
                <label for="sender_name" class="block text-xs font-medium uppercase tracking-[0.1em] text-[#78716C] mb-1.5">
                    Nama Pemilik Rekening Pengirim <span class="text-rose-500">*</span>
                </label>
                <input
                    type="text"
                    id="sender_name"
                    name="sender_name"
                    value="{{ old('sender_name', $payment->sender_name ?? auth()->user()->name) }}"
                    required
                    maxlength="100"
                    placeholder="Contoh: Budi Santoso"
                    class="block w-full rounded-2xl border @error('sender_name') border-rose-500 @else border-[#EFECE6] @enderror px-4 py-3 text-sm text-[#1C1917] placeholder-[#A8A29E] focus:border-[#1C1917] focus:outline-none focus:ring-1 focus:ring-[#1C1917] transition-all"
                >
                <p class="text-[11px] text-[#A8A29E] mt-1">
                    Nama yang tertera pada rekening atau akun bank yang Anda gunakan untuk mentransfer.
                </p>
                @error('sender_name')
                    <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Unggah Berkas Bukti Transfer --}}
            <div>
                <label class="block text-xs font-medium uppercase tracking-[0.1em] text-[#78716C] mb-1.5">
                    Foto / Tangkapan Layar Bukti Transfer <span class="text-rose-500">*</span>
                </label>

                {{-- Area Drag & Drop / File Picker --}}
                <div
                    id="dropZone"
                    class="border-2 border-dashed @error('proof') border-rose-400 bg-rose-50/20 @else border-[#D0C5B8] bg-[#FAF8F5]/60 hover:bg-[#FAF8F5] @enderror rounded-2xl p-6 sm:p-8 text-center transition-all cursor-pointer relative"
                >
                    <input
                        type="file"
                        id="proofInput"
                        name="proof"
                        accept="image/jpeg,image/png,image/webp,image/jpg"
                        required
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                    >

                    {{-- Placeholder State --}}
                    <div id="dropZonePrompt" class="space-y-3">
                        <div class="w-12 h-12 rounded-full bg-[#EDE7DF] text-[#1C1917] mx-auto flex items-center justify-center">
                            <x-icon name="upload" class="w-6 h-6" strokeWidth="1.75" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-[#1C1917]">
                                Klik untuk memilih berkas atau seret berkas ke sini
                            </p>
                            <p class="text-xs text-[#78716C] mt-1">
                                Format: JPG, JPEG, PNG, WEBP. Maksimal ukuran 2MB.
                            </p>
                        </div>
                    </div>

                    {{-- Pratinjau Gambar Setelah Dipilih --}}
                    <div id="previewContainer" class="hidden space-y-4">
                        <div class="relative inline-block mx-auto max-w-xs">
                            <img
                                id="imagePreview"
                                src="#"
                                alt="Pratinjau Bukti Transfer"
                                class="rounded-xl max-h-56 mx-auto object-contain shadow-xs border border-[#EFECE6]"
                            >
                        </div>
                        <div>
                            <p id="fileName" class="text-xs font-mono font-medium text-[#1C1917] truncate max-w-sm mx-auto"></p>
                            <p id="fileSize" class="text-[11px] text-[#78716C] mt-0.5"></p>
                        </div>
                        <button
                            type="button"
                            id="btnChangeImage"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-[#1C1917] bg-white border border-[#D0C5B8] rounded-full hover:bg-[#FAF8F5] transition-colors relative z-10"
                        >
                            <x-icon name="pencil" class="w-3.5 h-3.5" />
                            <span>Ganti Gambar</span>
                        </button>
                    </div>
                </div>

                @error('proof')
                    <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tombol Aksi --}}
            <div class="pt-4 border-t border-[#F3EFEA] flex flex-col sm:flex-row items-center justify-end gap-3">
                <a
                    href="{{ route('pelanggan.payments.show', $order->order_number) }}"
                    class="w-full sm:w-auto text-center px-6 py-3 text-sm font-medium text-[#78716C] hover:text-[#1C1917] rounded-full transition-colors"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    id="submitBtn"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3 bg-[#1C1917] hover:bg-black text-white text-sm font-medium rounded-full shadow-xs transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <x-icon name="upload" class="w-4 h-4" />
                    <span id="btnText">Unggah Bukti Pembayaran</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fileInput = document.getElementById('proofInput');
        const dropZonePrompt = document.getElementById('dropZonePrompt');
        const previewContainer = document.getElementById('previewContainer');
        const imagePreview = document.getElementById('imagePreview');
        const fileNameEl = document.getElementById('fileName');
        const fileSizeEl = document.getElementById('fileSize');
        const form = document.getElementById('uploadProofForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnChangeImage = document.getElementById('btnChangeImage');

        if (fileInput) {
            fileInput.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (file) {
                    // Validasi tipe file sisi klien
                    if (!file.type.match('image.*')) {
                        alert('Harap pilih berkas berupa gambar (JPG, PNG, atau WEBP).');
                        fileInput.value = '';
                        return;
                    }

                    // Validasi ukuran sisi klien (2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Ukuran berkas tidak boleh lebih dari 2MB.');
                        fileInput.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function (event) {
                        imagePreview.src = event.target.result;
                        fileNameEl.textContent = file.name;
                        fileSizeEl.textContent = (file.size / 1024).toFixed(1) + ' KB';

                        dropZonePrompt.classList.add('hidden');
                        previewContainer.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        if (btnChangeImage && fileInput) {
            btnChangeImage.addEventListener('click', function (e) {
                e.stopPropagation();
                fileInput.click();
            });
        }

        if (form && submitBtn) {
            form.addEventListener('submit', function () {
                submitBtn.disabled = true;
                if (btnText) {
                    btnText.textContent = 'Sedang Mengunggah...';
                }
            });
        }
    });
</script>
@endpush
@endsection
