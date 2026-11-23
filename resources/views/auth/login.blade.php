@extends('layouts.guest')

@section('title', 'Masuk - Modeva')

@section('content')
<div class="sm:mx-auto sm:w-full sm:max-w-md">
    <div class="text-center">
        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Modeva</h1>
        <p class="mt-2 text-sm text-slate-600">Silakan masuk ke akun Anda</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow-sm border border-slate-200 sm:rounded-lg sm:px-10">
            {{-- Flash status notification --}}
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700 border border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700 border border-red-200">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" id="loginForm" class="space-y-6">
                @csrf

                {{-- Email field --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">
                        Email
                    </label>
                    <div class="mt-1">
                        <input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="username"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            class="block w-full appearance-none rounded-md border @error('email') border-red-500 @else border-slate-300 @enderror px-3 py-2 placeholder-slate-400 shadow-sm focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 sm:text-sm"
                            placeholder="nama@email.com"
                        >
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password field with toggle --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">
                        Password
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="block w-full appearance-none rounded-md border @error('password') border-red-500 @else border-slate-300 @enderror px-3 py-2 pr-10 placeholder-slate-400 focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 sm:text-sm"
                            placeholder="••••••••"
                        >
                        <button
                            type="button"
                            id="togglePassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none"
                            aria-label="Tampilkan atau sembunyikan password"
                        >
                            {{-- Icon Show --}}
                            <svg id="eyeIcon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            {{-- Icon Hide --}}
                            <svg id="eyeSlashIcon" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember me checkbox --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input
                            id="remember"
                            name="remember"
                            type="checkbox"
                            value="1"
                            {{ old('remember') ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                        >
                        <label for="remember" class="ml-2 block text-sm text-slate-700 select-none">
                            Ingat saya
                        </label>
                    </div>
                </div>

                {{-- Submit button with disabled state --}}
                <div>
                    <button
                        type="submit"
                        id="submitBtn"
                        class="flex w-full justify-center rounded-md bg-slate-900 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 transition-all duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span id="btnText">Masuk</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.getElementById('togglePassword');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeSlashIcon = document.getElementById('eyeSlashIcon');

        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function () {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                eyeIcon.classList.toggle('hidden', isPassword);
                eyeSlashIcon.classList.toggle('hidden', !isPassword);
            });
        }

        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');

        if (loginForm && submitBtn) {
            loginForm.addEventListener('submit', function () {
                submitBtn.disabled = true;
                if (btnText) {
                    btnText.textContent = 'Memproses...';
                }
            });
        }
    });
</script>
@endpush
@endsection
