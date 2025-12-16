<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $dw = DB::connection('dw');

        // Tahun awal & tahun terbaru
        $firstYear = $dw->table('dim_time')->min('tahun');
        $currentYear = $dw->table('dim_time')->max('tahun');

        // ==== Total Keseluruhan ====
        $totalSales = $dw->table('tf_sales')->sum('sales_amount');
        $totalPurchasing = $dw->table('tf_purchasing')->sum('total_cost');
        $totalProfit = $totalSales - $totalPurchasing;

        // ==== Profit Tahun Pertama ====
        $firstYearSales = $dw->table('tf_sales as s')
            ->join('dim_time as t', 's.time_id', '=', 't.time_id')
            ->where('t.tahun', $firstYear)
            ->sum('s.sales_amount');

        $firstYearPurchasing = $dw->table('tf_purchasing as p')
            ->join('dim_time as t', 'p.time_id', '=', 't.time_id')
            ->where('t.tahun', $firstYear)
            ->sum('p.total_cost');

        $firstYearProfit = $firstYearSales - $firstYearPurchasing;

        $roi = ($firstYearProfit != 0)
            ? (($totalProfit / abs($firstYearProfit)) * 100)
            : 0;

        // ==== Total Transaksi ====
        $totalSalesTransaction = $dw->table('tf_sales')->count();
        $totalPurchasingTransaction = $dw->table('tf_purchasing')->count();

        // ==== 3 tahun terbaru ====
        $last3Years = $dw->table('dim_time')
            ->select('tahun')->distinct()
            ->orderBy('tahun', 'desc')
            ->limit(3)
            ->pluck('tahun')
            ->toArray();

        // ==================================================
        // SALES PER BULAN
        // ==================================================
        $sales = $dw->table('tf_sales as s')
            ->join('dim_time as t', 's.time_id', '=', 't.time_id')
            ->whereIn('t.tahun', $last3Years)
            ->selectRaw("
                CONCAT(LPAD(t.bulan, 2, '0'), '/', RIGHT(t.tahun, 2)) AS bulan_tahun,
                SUM(s.sales_amount) AS total_sales
            ")
            ->groupBy('t.tahun', 't.bulan')
            ->orderBy('t.tahun')
            ->orderBy('t.bulan')
            ->get()
            ->keyBy('bulan_tahun');

        // ==================================================
        // PURCHASING PER BULAN
        // ==================================================
        $purchasing = $dw->table('tf_purchasing as p')
            ->join('dim_time as t', 'p.time_id', '=', 't.time_id')
            ->whereIn('t.tahun', $last3Years)
            ->selectRaw("
                CONCAT(LPAD(t.bulan, 2, '0'), '/', RIGHT(t.tahun, 2)) AS bulan_tahun,
                SUM(p.total_cost) AS total_purchasing
            ")
            ->groupBy('t.tahun', 't.bulan')
            ->orderBy('t.tahun')
            ->orderBy('t.bulan')
            ->get()
            ->keyBy('bulan_tahun');

        // ==================================================
        // PROFIT PER BULAN (GABUNG)
        // ==================================================
        $profitPerMonth = [];

        foreach ($sales as $bulan => $row) {
            $p = $purchasing[$bulan]->total_purchasing ?? 0;

            $profitPerMonth[] = [
                'bulan_tahun' => $bulan,
                'total_sales' => $row->total_sales,
                'total_purchasing' => $p,
                'profit' => $row->total_sales - $p,
            ];
        }

        // ==================================================
        // ==== PROFIT PER TAHUN (1 TITIK PER TAHUN) ====
        // ==================================================
        $profitPerYear = $dw->table('dim_time')
            ->select('tahun')
            ->distinct()
            ->orderBy('tahun')
            ->get()
            ->map(function ($row) use ($dw) {
                $sales = $dw->table('tf_sales as s')
                    ->join('dim_time as t', 's.time_id', '=', 't.time_id')
                    ->where('t.tahun', $row->tahun)
                    ->sum('s.sales_amount');

                $purch = $dw->table('tf_purchasing as p')
                    ->join('dim_time as t', 'p.time_id', '=', 't.time_id')
                    ->where('t.tahun', $row->tahun)
                    ->sum('p.total_cost');

                return [
                    'tahun' => $row->tahun,
                    'total_sales' => $sales,
                    'total_purchasing' => $purch,
                    'profit' => $sales - $purch,
                ];
            });

// ==================================================
// Top 10 Pelanggan Berdasarkan Total Pembelian
// ==================================================
$topCustomers = $dw->table('dim_customer as c')
    ->join('tf_sales as s', 'c.CustomerID', '=', 's.CustomerID')
    ->select('c.CustomerID', 'c.customer_name as name', DB::raw('SUM(s.sales_amount) as total_purchase'))
    ->groupBy('c.CustomerID', 'c.customer_name')
    ->orderByDesc('total_purchase')
    ->limit(10)
    ->get();


        return view('dashboard', [
            'firstYear' => $firstYear,
            'currentYear' => $currentYear,

            'totalSales' => $totalSales,
            'totalPurchasing' => $totalPurchasing,
            'totalProfit' => $totalProfit,

            'roi' => $roi,
            'totalSalesTransaction' => $totalSalesTransaction,
            'totalPurchasingTransaction' => $totalPurchasingTransaction,

            'last3Years' => $last3Years,

            'profitPerMonth' => $profitPerMonth,
            'profitPerYear' => $profitPerYear, // <= INI DIA TITIK PER TAHUN
            'topCustomers' => $topCustomers,
        ]);
    }
}
