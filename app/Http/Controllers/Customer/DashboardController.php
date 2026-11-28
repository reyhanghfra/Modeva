<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\Customer\CustomerDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dasbor akun pelanggan.
     */
    public function index(Request $request, CustomerDashboardService $service): View
    {
        $user = $request->user();

        $statusCounts = $service->countsByStatus($user);
        $needsAction = $service->needsAction($user);
        $recentOrders = $service->recentOrders($user);

        return view('pelanggan.dashboard', compact('user', 'statusCounts', 'needsAction', 'recentOrders'));
    }
}
