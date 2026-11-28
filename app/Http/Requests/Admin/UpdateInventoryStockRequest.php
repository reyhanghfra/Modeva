<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'operation' => ['required', 'in:set,add,subtract'],
            'quantity' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'low_stock_threshold' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ];
    }

    public function messages(): array
    {
        return [
            'operation.required' => 'Pilih cara perubahan stok.',
            'operation.in' => 'Cara perubahan stok tidak valid.',
            'quantity.required' => 'Jumlah stok wajib diisi.',
            'quantity.integer' => 'Jumlah stok harus berupa bilangan bulat.',
            'quantity.min' => 'Jumlah stok tidak boleh kurang dari nol.',
            'low_stock_threshold.required' => 'Batas stok menipis wajib diisi.',
        ];
    }
}
