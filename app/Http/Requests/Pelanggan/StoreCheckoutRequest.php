<?php

namespace App\Http\Requests\Pelanggan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'pelanggan';
    }

    public function rules(): array
    {
        $user = $this->user();
        $hasProfileAddress = filled($user?->customer?->address);
        $hasSavedAddresses = $user ? $user->addresses()->exists() : false;
        $hasAnySavedAddress = $hasProfileAddress || $hasSavedAddresses;

        return [
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_phone' => ['required', 'string', 'max:25', 'regex:/^[0-9+\-\s()]{8,25}$/'],
            'address_option' => [
                $hasAnySavedAddress ? 'required' : 'nullable',
                'string',
                Rule::in(['profile', 'saved_address', 'custom']),
            ],
            'selected_address_id' => [
                Rule::requiredIf(fn () => $this->input('address_option') === 'saved_address'),
                'nullable',
                'integer',
                'exists:customer_addresses,id',
            ],
            'shipping_address' => [
                Rule::requiredIf(fn () => ! $hasAnySavedAddress || $this->input('address_option') === 'custom'),
                'nullable',
                'string',
                'max:1000',
            ],
            'shipping_service' => [
                'required',
                'string',
                Rule::in(['JNE Reguler', 'J&T Express', 'SiCepat BEST', 'JNE YES']),
            ],
            'payment_method' => ['required', 'string', Rule::in(['transfer'])],
            'bank_name' => ['required', 'string', Rule::in(['BCA', 'Mandiri', 'BRI', 'BNI'])],
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_name.required' => 'Nama penerima wajib diisi.',
            'recipient_name.max' => 'Nama penerima maksimal 255 karakter.',
            'recipient_phone.required' => 'Nomor telepon penerima wajib diisi.',
            'recipient_phone.max' => 'Nomor telepon maksimal 25 karakter.',
            'recipient_phone.regex' => 'Format nomor telepon tidak valid (minimal 8 digit).',
            'address_option.required' => 'Silakan pilih alamat pengiriman.',
            'address_option.in' => 'Pilihan alamat pengiriman tidak valid.',
            'selected_address_id.required' => 'Silakan pilih salah satu alamat tersimpan.',
            'selected_address_id.exists' => 'Alamat yang dipilih tidak ditemukan.',
            'shipping_address.required' => 'Alamat pengiriman lengkap wajib diisi.',
            'shipping_address.max' => 'Alamat pengiriman maksimal 1000 karakter.',
            'shipping_service.required' => 'Jasa pengiriman wajib dipilih.',
            'shipping_service.in' => 'Jasa pengiriman yang dipilih tidak tersedia.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
            'bank_name.required' => 'Bank tujuan transfer wajib dipilih.',
            'bank_name.in' => 'Bank yang dipilih tidak tersedia.',
        ];
    }
}
