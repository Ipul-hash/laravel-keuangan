<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Transaction;

class PengeluaranController extends Controller
{
    /**
     * Display a listing of pengeluaran.
     */
    public function index(Request $request)
    {
        $tanggal  = $request->query('tanggal', now()->toDateString());
        $walletId = $request->query('wallet_id'); // filter manual kalau mau liat ATM tertentu

        $pengeluaran = Auth::user()->transactions()
            ->where('type', 'pengeluaran')
            ->whereDate('date', $tanggal)
            ->when($walletId, fn($q) => $q->where('wallet_id', $walletId))
            ->get();

        return response()->json([
            'data'  => $pengeluaran,
            'total' => $pengeluaran->sum('amount')
        ]);
    }

    /**
     * Store a newly created pengeluaran.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wallet_id'   => 'required|exists:wallets,id',
            'description' => 'required|string|max:255',
            'category'    => 'nullable|string|max:100',
            'amount'      => 'required|numeric|min:0.01',
            'date'        => 'required|date|before_or_equal:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $pengeluaran = Auth::user()->transactions()->create([
            'wallet_id'   => $request->wallet_id,
            'description' => $request->description,
            'category_id' => null,
            'amount'      => $request->amount,
            'type'        => 'pengeluaran',
            'date'        => $request->date,
        ]);

        return response()->json([
            'message' => 'Pengeluaran berhasil ditambahkan',
            'data'    => $pengeluaran
        ], 201);
    }

    /**
     * Update the specified pengeluaran.
     */
    public function update(Request $request, $id)
    {
        $pengeluaran = Auth::user()->transactions()
            ->where('type', 'pengeluaran')
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'wallet_id'   => 'required|exists:wallets,id',
            'description' => 'required|string|max:255',
            'category'    => 'nullable|string|max:100',
            'amount'      => 'required|numeric|min:0.01',
            'date'        => 'required|date|before_or_equal:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $pengeluaran->update([
            'wallet_id'   => $request->wallet_id,
            'description' => $request->description,
            'category_id' => null,
            'amount'      => $request->amount,
            'date'        => $request->date,
        ]);

        return response()->json([
            'message' => 'Pengeluaran berhasil diupdate',
            'data'    => $pengeluaran
        ]);
    }

    /**
     * Remove the specified pengeluaran.
     */
    public function destroy($id)
    {
        $pengeluaran = Auth::user()->transactions()
            ->where('type', 'pengeluaran')
            ->findOrFail($id);

        $pengeluaran->delete();

        return response()->json([
            'message' => 'Pengeluaran berhasil dihapus'
        ]);
    }

    /**
     * Show the specified pengeluaran.
     */
    public function show($id)
    {
        $pengeluaran = Auth::user()->transactions()
            ->where('type', 'pengeluaran')
            ->findOrFail($id);

        return response()->json([
            'data' => $pengeluaran
        ]);
    }
}