<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PhotoController extends Controller
{
    public function edit(): View
    {
        $s = DB::table('site_settings')->pluck('value', 'key');
        $item = fn ($label, $type, $path, $id = null) => compact('label', 'type', 'path', 'id');

        $groups = [
            'Beranda' => [
                $item('Foto Hero (banner utama)', 'hero', $s['hero'] ?? null),
                $item('Foto Banner Promo', 'promo', $s['promo'] ?? null),
            ],
            'Kategori' => Category::orderBy('name')->get()
                ->map(fn ($c) => $item($c->name, 'category', $c->image, $c->id))->all(),
            'Produk' => Product::orderBy('name')->get()
                ->map(fn ($p) => $item($p->name, 'product', $p->image, $p->id))->all(),
        ];

        return view('admin.photos.edit', compact('groups'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => 'required|in:hero,promo,category,product',
            'id' => 'required_if:type,category,product|integer',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'image.required' => 'Pilih file foto terlebih dahulu.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format foto harus JPG, PNG, atau WebP.',
            'image.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $path = $request->file('image')->store('site', 'public');

        if (in_array($data['type'], ['hero', 'promo'], true)) {
            $old = DB::table('site_settings')->where('key', $data['type'])->value('value');
            DB::table('site_settings')->updateOrInsert(['key' => $data['type']], ['value' => $path]);
        } else {
            $model = ($data['type'] === 'category' ? Category::class : Product::class)::findOrFail($data['id']);
            $old = $model->image;
            $model->update(['image' => $path]);
        }

        if ($old) {
            Storage::disk('public')->delete($old);
        }

        return back()->with('success', 'Foto berhasil diperbarui.');
    }
}