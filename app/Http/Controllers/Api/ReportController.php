<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DailySummary;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    // GET /api/reports/monthly?year=2025&month=9
    public function monthly(Request $request)
    {
        $request->validate([
            'year' => 'nullable|integer',
            'month' => 'nullable|integer|min:1|max:12',
        ]);

        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $data = DailySummary::where('user_id', Auth::id())
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get();

        $totalPemasukan = $data->sum('pemasukan');
        $totalPengeluaran = $data->sum('pengeluaran');
        $saldo = $totalPemasukan - $totalPengeluaran;

        return response()->json([
            'data' => $data,
            'meta' => [
                'total_pemasukan' => $totalPemasukan,
                'total_pengeluaran' => $totalPengeluaran,
                'saldo' => $saldo,
                'year' => (int)$year,
                'month' => (int)$month,
            ]
        ]);
    }

    // GET /api/reports/monthly/csv?year=2025&month=9
    public function monthlyCsv(Request $request)
    {
        $request->validate([
            'year' => 'nullable|integer',
            'month' => 'nullable|integer|min:1|max:12',
        ]);

        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $rows = DailySummary::where('user_id', Auth::id())
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get();

        $filename = "report-{$year}-{$month}.csv";

        $response = new StreamedResponse(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['date','pemasukan','pengeluaran','saldo']);
            foreach ($rows as $r) {
                fputcsv($handle, [
                    $r->date->toDateString(),
                    number_format($r->pemasukan,2,'.',''),
                    number_format($r->pengeluaran,2,'.',''),
                    number_format($r->saldo,2,'.',''),
                ]);
            }
            fclose($handle);
        });

        $response->headers->set('Content-Type','text/csv');
        $response->headers->set('Content-Disposition','attachment; filename="'.$filename.'"');

        return $response;
    }
}
