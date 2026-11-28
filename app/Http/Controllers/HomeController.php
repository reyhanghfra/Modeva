<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $products = fn () => Product::where('is_active', true)->withTotalStock();

        return view('pelanggan.beranda', [
            'photos' => DB::table('site_settings')->pluck('value', 'key'),
            'categories' => Category::active()->orderBy('name')->get(),
            'newest' => $products()->latest('id')->take(4)->get(),
            'bestSellers' => $products()
                ->addSelect(['sold' => OrderItem::selectRaw('coalesce(sum(quantity), 0)')->whereColumn('product_id', 'products.id')])
                ->orderByDesc('sold')->orderBy('id')->take(4)->get(),
        ]);
    }
}