<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidationValidator;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'sku' => ['required', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($product)],
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'price' => ['required', 'integer', 'min:0', 'max:9999999999'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_active' => ['required', 'boolean'],
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => [
                'sometimes',
                'required',
                'integer',
                'distinct',
                Rule::exists('product_variants', 'id')->where('product_id', $product->id),
            ],
            'variants.*.color' => ['required', 'string', 'max:255'],
            'variants.*.size' => ['required', 'string', 'max:255'],
            'variants.*.stock' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'variants.*.low_stock_threshold' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ];
    }

    public function after(): array
    {
        return [
            function (ValidationValidator $validator): void {
                $variants = $this->input('variants');
                $product = $this->route('product');

                if (! is_array($variants) || $validator->errors()->has('variants')) {
                    return;
                }

                $submittedVariantIds = collect($variants)
                    ->filter(fn ($variant): bool => is_array($variant)
                        && filter_var($variant['id'] ?? null, FILTER_VALIDATE_INT) !== false)
                    ->pluck('id')
                    ->map(fn ($id): int => (int) $id)
                    ->all();
                $existingVariants = $product->variants()
                    ->whereIn('id', $submittedVariantIds)
                    ->get(['id', 'color', 'size'])
                    ->keyBy('id');
                $existingCombinations = [];

                foreach ($existingVariants as $existingVariant) {
                    $key = mb_strtolower(trim($existingVariant->color)).'|'.mb_strtolower(trim($existingVariant->size));
                    $existingCombinations[$key] = $existingVariant->id;
                }

                $seen = [];

                foreach ($variants as $index => $variant) {
                    if (! is_array($variant)
                        || ! is_string($variant['color'] ?? null)
                        || ! is_string($variant['size'] ?? null)) {
                        continue;
                    }

                    $key = mb_strtolower(trim($variant['color'])).'|'.mb_strtolower(trim($variant['size']));
                    $variantId = filter_var($variant['id'] ?? null, FILTER_VALIDATE_INT);
                    $variantId = $variantId === false ? null : $variantId;

                    if (isset($seen[$key]) || (isset($existingCombinations[$key]) && $existingCombinations[$key] !== $variantId)) {
                        $validator->errors()->add("variants.{$index}.color", 'Kombinasi warna dan ukuran sudah digunakan.');
                    }

                    $seen[$key] = true;
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori produk wajib dipilih.',
            'sku.required' => 'SKU produk wajib diisi.',
            'sku.unique' => 'SKU tersebut sudah digunakan.',
            'name.required' => 'Nama produk wajib diisi.',
            'brand.required' => 'Merek produk wajib diisi.',
            'description.required' => 'Deskripsi produk wajib diisi.',
            'price.required' => 'Harga produk wajib diisi.',
            'image.image' => 'Foto produk harus berupa gambar.',
            'image.mimes' => 'Foto produk harus berformat JPG, PNG, atau WebP.',
            'image.max' => 'Ukuran foto produk maksimal 5 MB.',
            'variants.required' => 'Tambahkan minimal satu varian produk.',
            'variants.min' => 'Tambahkan minimal satu varian produk.',
            'variants.*.id.exists' => 'Varian yang dipilih bukan milik produk ini.',
            'variants.*.color.required' => 'Warna varian wajib diisi.',
            'variants.*.size.required' => 'Ukuran varian wajib diisi.',
            'variants.*.stock.required' => 'Stok varian wajib diisi.',
            'variants.*.low_stock_threshold.required' => 'Batas stok menipis wajib diisi.',
        ];
    }
}
