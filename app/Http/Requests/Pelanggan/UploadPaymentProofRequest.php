<?php

namespace App\Http\Requests\Pelanggan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadPaymentProofRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi pengunggahan bukti pembayaran transfer.
     */
    public function rules(): array
    {
        $validBankNames = array_column(config('payment.banks', []), 'bank_name');

        return [
            'bank_name' => [
                'required',
                'string',
                Rule::in($validBankNames),
            ],
            'sender_name' => [
                'required',
                'string',
                'max:100',
            ],
            'proof' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048', // Maksimal 2MB (2048 KB)
            ],
        ];
    }

    /**
     * Pesan validasi berbahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'bank_name.required' => 'Bank tujuan transfer wajib dipilih.',
            'bank_name.in' => 'Bank tujuan transfer yang dipilih tidak valid.',
            'sender_name.required' => 'Nama pemilik rekening pengirim wajib diisi.',
            'sender_name.string' => 'Nama pemilik rekening harus berupa teks.',
            'sender_name.max' => 'Nama pemilik rekening maksimal 100 karakter.',
            'proof.required' => 'Berkas bukti transfer wajib diunggah.',
            'proof.image' => 'Berkas bukti transfer harus berupa gambar.',
            'proof.mimes' => 'Format gambar yang didukung hanya JPG, JPEG, PNG, atau WEBP.',
            'proof.max' => 'Ukuran berkas bukti transfer tidak boleh melebihi 2MB.',
        ];
    }
}
