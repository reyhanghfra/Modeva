<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Menampilkan halaman Laporan Penjualan dengan data nyata dari orders & order_items.
     */
    public function index(Request $request, ReportService $reportService): View
    {
        [$start, $end, $isFiltered] = $this->resolvePeriod($request);

        $reportService->recordActivity(
            $isFiltered ? 'Memfilter laporan penjualan' : 'Melihat laporan penjualan',
            $request->user(),
            $start,
            $end,
            $isFiltered,
            $request->ip()
        );

        $summary = $reportService->summary($start, $end);
        $topProducts = $reportService->topProducts($start, $end);
        $chartData = $reportService->chartData($start, $end, $isFiltered);
        $chartIsDaily = $isFiltered && $start->diffInDays($end) <= 62;
        $recentActivities = $reportService->recentActivities();

        return view('admin.reports.index', compact(
            'summary',
            'topProducts',
            'chartData',
            'chartIsDaily',
            'recentActivities',
            'start',
            'end',
            'isFiltered',
        ));
    }

    /**
     * Mengekspor laporan penjualan periode terpilih ke format Excel (.xls).
     */
    public function export(Request $request, ReportService $reportService): Response
    {
        [$start, $end, $isFiltered] = $this->resolvePeriod($request);

        $summary = $reportService->summary($start, $end);
        $topProducts = $reportService->topProducts($start, $end, 50);
        $orders = $reportService->completedOrders($start, $end);

        $filename = sprintf(
            'laporan-penjualan-%s-sd-%s.xls',
            $start->format('Y-m-d'),
            $end->format('Y-m-d')
        );

        $reportService->recordActivity(
            'Mengekspor laporan penjualan (Excel)',
            $request->user(),
            $start,
            $end,
            $isFiltered,
            $request->ip(),
            ['filename' => $filename]
        );

        $content = view('admin.reports.export-excel', compact(
            'summary',
            'topProducts',
            'orders',
            'start',
            'end',
            'isFiltered',
        ))->render();

        return response("\xEF\xBB\xBF".$content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0, no-cache, must-revalidate',
        ]);
    }

    /**
     * Mengurai rentang tanggal filter dari request (default: bulan berjalan).
     *
     * @return array{0: Carbon, 1: Carbon, 2: bool}
     */
    private function resolvePeriod(Request $request): array
    {
        $isFiltered = $request->filled('start_date') || $request->filled('end_date');

        $start = now()->startOfMonth();
        if ($request->filled('start_date')) {
            try {
                $start = Carbon::parse((string) $request->input('start_date'))->startOfDay();
            } catch (\Throwable) {
                $start = now()->startOfMonth();
            }
        }

        $end = now()->endOfDay();
        if ($request->filled('end_date')) {
            try {
                $end = Carbon::parse((string) $request->input('end_date'))->endOfDay();
            } catch (\Throwable) {
                $end = now()->endOfDay();
            }
        }

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return [$start, $end, $isFiltered];
    }
}
