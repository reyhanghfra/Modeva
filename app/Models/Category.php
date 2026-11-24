<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Model Category (Kategori Produk).
 *
 * CATATAN INTEGRASI:
 * Tabel dan model ini dipakai bersama dengan modul Produk (Orang 2)
 * melalui foreign key `products.category_id`. Pastikan skema kolom
 * name, slug, description, dan is_active tetap konsisten.
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Boot model untuk generate slug otomatis dan tangani bentrok slug.
     */
    protected static function booted(): void
    {
        static::saving(function (Category $category) {
            if ($category->isDirty('name') || empty($category->slug)) {
                $category->slug = static::generateUniqueSlug($category->name, $category->id);
            }
        });
    }

    /**
     * Generate slug unik dari nama dengan menangani bentrok.
     */
    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Scope untuk kategori yang aktif.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Relasi ke produk.
     * TODO: Definisikan relasi hasMany setelah tabel dan model Product dibuat oleh Orang 2.
     */
    public function products()
    {
        if (class_exists(\App\Models\Product::class)) {
            return $this->hasMany(\App\Models\Product::class);
        }

        return null;
    }

    /**
     * Memeriksa apakah kategori aman untuk dihapus (tidak memiliki produk terkait).
     */
    public function canBeDeleted(): bool
    {
        if (Schema::hasTable('products')) {
            // TODO: cek keterkaitan dengan tabel products (milik Orang 2)
            return ! DB::table('products')->where('category_id', $this->id)->exists();
        }

        return true;
    }
}
