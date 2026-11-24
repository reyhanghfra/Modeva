<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardStatsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman Ringkasan (dasbor admin).
     */
    public function index(DashboardStatsService $statsService): View
    {
        $summary = $statsService->summary();
        $salesChart = $statsService->salesLast7Days();
        $lowStock = $statsService->lowStock();
        $recentTransactions = $statsService->recentTransactions();
        $topProducts = $statsService->topProducts();

        return view('admin.dashboard', compact(
            'summary',
            'salesChart',
            'lowStock',
            'recentTransactions',
            'topProducts'
        ));
    }
}
