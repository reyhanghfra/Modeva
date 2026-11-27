<?php

namespace App\Http\Requests\Pelanggan;

use Illuminate\Foundation\Http\FormRequest;

class SaveCustomerAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'pelanggan';
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_primary' => $this->boolean('is_primary'),
        ]);
    }

    public function rules(): array
    {
        return [
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:25', 'regex:/^[0-9+\-\s()]{8,25}$/'],
            'address' => ['required', 'string', 'max:1000'],
            'district' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'regex:/^[0-9]{5}$/'],
            'is_primary' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_name.required' => 'Nama penerima wajib diisi.',
            'recipient_name.max' => 'Nama penerima maksimal 255 karakter.',
            'phone.required' => 'Nomor telepon penerima wajib diisi.',
            'phone.max' => 'Nomor telepon maksimal 25 karakter.',
            'phone.regex' => 'Format nomor telepon tidak valid (minimal 8 digit).',
            'address.required' => 'Alamat lengkap wajib diisi.',
            'address.max' => 'Alamat lengkap maksimal 1000 karakter.',
            'district.required' => 'Kecamatan wajib diisi.',
            'district.max' => 'Nama kecamatan maksimal 100 karakter.',
            'city.required' => 'Kota/Kabupaten wajib diisi.',
            'city.max' => 'Nama kota/kabupaten maksimal 100 karakter.',
            'province.required' => 'Provinsi wajib diisi.',
            'province.max' => 'Nama provinsi maksimal 100 karakter.',
            'postal_code.required' => 'Kode pos wajib diisi.',
            'postal_code.regex' => 'Kode pos harus terdiri dari 5 digit angka.',
        ];
    }
}
