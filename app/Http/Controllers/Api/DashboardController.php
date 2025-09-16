<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\SavingGoal;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 👇 AMBIL WALLET AKTIF SAJA — INI KUNCI UTAMANYA!
        $activeWallet = $user->wallets()->where('is_active', 1)->first();

        if (!$activeWallet) {
            // Fallback: jika tidak ada wallet aktif, ambil wallet pertama
            $activeWallet = $user->wallets()->first();
        }

        $walletId = $activeWallet ? $activeWallet->id : null;

        // 🟢 SALDO (hanya dari wallet aktif)
        $totalPemasukan = Transaction::where('wallet_id', $walletId)
            ->where('type', 'pemasukan')
            ->sum('amount');

        $totalPengeluaran = Transaction::where('wallet_id', $walletId)
            ->where('type', 'pengeluaran')
            ->sum('amount');

        $saldo = $totalPemasukan - $totalPengeluaran;

        // 🔄 Hari ini (hanya dari wallet aktif)
        $today = now()->toDateString();

        $pemasukanHariIni = Transaction::where('wallet_id', $walletId)
            ->where('type', 'pemasukan')
            ->whereDate('date', $today)
            ->sum('amount');

        $pengeluaranHariIni = Transaction::where('wallet_id', $walletId)
            ->where('type', 'pengeluaran')
            ->whereDate('date', $today)
            ->sum('amount');

        // 📋 Recent transaksi (hanya dari wallet aktif)
        $recent = Transaction::where('wallet_id', $walletId)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $savingGoals = SavingGoal::where('user_id', $user->id)
    ->where('is_active', true)
    ->get()
    ->map(function ($goal) {
        $progress = $goal->target_amount > 0
            ? round(($goal->current_amount / $goal->target_amount) * 100, 2)
            : 0;

        if ($progress > 100) {
            $progress = 100;
        }

        return [
            'id'             => $goal->id,
            'title'          => $goal->title,
            'target_amount'  => $goal->target_amount,
            'current_amount' => $goal->current_amount,
            'deadline'       => $goal->deadline,
            'progress'       => $progress,
        ];
    });

        return response()->json([
            'saldo'       => $saldo,
            'pemasukan'   => $pemasukanHariIni,
            'pengeluaran' => $pengeluaranHariIni,
            'recent'      => $recent,
            'goals'       => $savingGoals,
            'today'       => $today,
        ]);
    }
}