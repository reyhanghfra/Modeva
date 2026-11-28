@extends('layouts.guest')

@section('title', 'Daftar Akun Baru - Modeva')

@section('content')
<div class="sm:mx-auto sm:w-full sm:max-w-md">
    <div class="text-center">
        <a href="{{ route('home') }}" class="inline-block">
            <span class="font-serif text-3xl font-bold tracking-[0.25em] text-slate-900">MODEVA</span>
        </a>
        <h1 class="mt-4 font-serif text-2xl font-medium text-slate-900">Buat Akun Baru</h1>
        <p class="mt-1.5 text-sm text-slate-600">Daftar untuk mulai memilih busana elegan favorit Anda</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow-sm border border-slate-200 sm:rounded-2xl sm:px-10">
            <form method="POST" action="{{ route('register.store') }}" id="registerForm" class="space-y-5">
                @csrf

                {{-- Nama Lengkap --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700">
                        Nama Lengkap
                    </label>
                    <div class="mt-1">
                        <input
                            id="name"
                            name="name"
                            type="text"
                            autocomplete="name"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            class="block w-full appearance-none rounded-xl border @error('name') border-red-500 @else border-slate-300 @enderror px-3.5 py-2.5 placeholder-slate-400 shadow-xs focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 sm:text-sm"
                            placeholder="Nama Anda"
                        >
                    </div>
                    @error('name')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Alamat Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">
                        Alamat Email
                    </label>
                    <div class="mt-1">
                        <input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            value="{{ old('email') }}"
                            required
                            class="block w-full appearance-none rounded-xl border @error('email') border-red-500 @else border-slate-300 @enderror px-3.5 py-2.5 placeholder-slate-400 shadow-xs focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 sm:text-sm"
                            placeholder="nama@email.com"
                        >
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">
                        Password
                    </label>
                    <div class="mt-1 relative rounded-xl shadow-xs">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="block w-full appearance-none rounded-xl border @error('password') border-red-500 @else border-slate-300 @enderror px-3.5 py-2.5 pr-10 placeholder-slate-400 focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 sm:text-sm"
                            placeholder="Minimal 8 karakter"
                        >
                        <button
                            type="button"
                            id="togglePassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none"
                            aria-label="Tampilkan atau sembunyikan password"
                        >
                            <svg id="eyeIcon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eyeSlashIcon" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Konfirmasi Password --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700">
                        Ulangi Password
                    </label>
                    <div class="mt-1 relative rounded-xl shadow-xs">
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="block w-full appearance-none rounded-xl border border-slate-300 px-3.5 py-2.5 pr-10 placeholder-slate-400 focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 sm:text-sm"
                            placeholder="Ulangi password di atas"
                        >
                        <button
                            type="button"
                            id="toggleConfirmPassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none"
                            aria-label="Tampilkan atau sembunyikan konfirmasi password"
                        >
                            <svg id="eyeConfirmIcon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eyeSlashConfirmIcon" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="pt-2">
                    <button
                        type="submit"
                        id="submitBtn"
                        class="flex w-full justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-medium text-white shadow-xs hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 transition-all duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span id="btnText">Daftar Akun</span>
                    </button>
                </div>
            </form>

            {{-- Link ke Halaman Login --}}
            <div class="mt-6 text-center border-t border-slate-100 pt-5">
                <p class="text-sm text-slate-600">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="font-medium text-slate-900 hover:underline underline-offset-4">
                        Masuk di sini
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function setupToggle(inputId, toggleBtnId, eyeId, slashId) {
            const input = document.getElementById(inputId);
            const btn = document.getElementById(toggleBtnId);
            const eye = document.getElementById(eyeId);
            const slash = document.getElementById(slashId);

            if (btn && input) {
                btn.addEventListener('click', function () {
                    const isPassword = input.type === 'password';
                    input.type = isPassword ? 'text' : 'password';
                    if (eye && slash) {
                        eye.classList.toggle('hidden', isPassword);
                        slash.classList.toggle('hidden', !isPassword);
                    }
                });
            }
        }

        setupToggle('password', 'togglePassword', 'eyeIcon', 'eyeSlashIcon');
        setupToggle('password_confirmation', 'toggleConfirmPassword', 'eyeConfirmIcon', 'eyeSlashConfirmIcon');

        const form = document.getElementById('registerForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');

        if (form && submitBtn) {
            form.addEventListener('submit', function () {
                submitBtn.disabled = true;
                if (btnText) {
                    btnText.textContent = 'Mendaftarkan...';
                }
            });
        }
    });
</script>
@endpush
@endsection
