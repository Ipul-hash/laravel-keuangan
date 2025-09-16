<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;

class LaporanController extends Controller
{
   public function index()
{
    $laporan = Transaction::selectRaw("
            YEAR(date) as tahun,
            MONTH(date) as bulan,
            SUM(CASE WHEN type = 'pemasukan' THEN amount ELSE 0 END) as total_pemasukan,
            SUM(CASE WHEN type = 'pengeluaran' THEN amount ELSE 0 END) as total_pengeluaran
        ")
        ->groupBy('tahun', 'bulan')
        ->orderBy('tahun', 'desc')
        ->orderBy('bulan', 'desc')
        ->get()
        ->map(function ($row) {
            $saldo = $row->total_pemasukan - $row->total_pengeluaran;
            return [
                'tahun' => $row->tahun,
                'bulan' => $row->bulan,
                'bulan_nama' => Carbon::createFromDate($row->tahun, $row->bulan, 1)->translatedFormat('F'),
                'pemasukan' => $row->total_pemasukan,
                'pengeluaran' => $row->total_pengeluaran,
                'saldo' => $saldo,
            ];
        });

    // Hitung total global (buat card atas)
    $totalPemasukan = $laporan->sum('pemasukan');
    $totalPengeluaran = $laporan->sum('pengeluaran');
    $totalSaldo = $totalPemasukan - $totalPengeluaran;

    return view('laporan.index', compact('laporan', 'totalPemasukan', 'totalPengeluaran', 'totalSaldo'));
}


    public function show($tahun, $bulan)
    {
        $transaksi = Transaction::whereYear('date', $tahun)
            ->whereMonth('date', $bulan)
            ->orderBy('date', 'desc')
            ->get();

        $bulanNama = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F');

        return view('laporan.show', compact('transaksi', 'tahun', 'bulan', 'bulanNama'));
    }
}
