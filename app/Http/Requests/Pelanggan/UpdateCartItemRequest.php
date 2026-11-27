<?php

namespace App\Http\Requests\Pelanggan;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'pelanggan';
    }

    public function rules(): array
    {
        return [
            'action' => ['nullable', 'string', 'in:increase,decrease'],
            'quantity' => ['required_without:action', 'nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'action.in' => 'Aksi pembaruan jumlah tidak valid.',
            'quantity.required_without' => 'Jumlah produk wajib diisi.',
            'quantity.integer' => 'Jumlah produk harus berupa angka.',
            'quantity.min' => 'Jumlah produk minimal 1.',
        ];
    }
}
