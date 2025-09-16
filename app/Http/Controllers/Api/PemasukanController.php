<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Transaction;

class PemasukanController extends Controller
{
    /**
     * Display a listing of pemasukan.
     */
    public function index(Request $request)
    {
        $tanggal  = $request->query('tanggal', now()->toDateString());
        $walletId = $request->query('wallet_id'); // filter manual kalau mau liat ATM tertentu

        $pemasukan = Auth::user()->transactions()
            ->where('type', 'pemasukan')
            ->whereDate('date', $tanggal)
            ->when($walletId, fn($q) => $q->where('wallet_id', $walletId))
            ->get();

        return response()->json([
            'data'  => $pemasukan,
            'total' => $pemasukan->sum('amount')
        ]);
    }

    /**
     * Store a newly created pemasukan.
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

        $pemasukan = Auth::user()->transactions()->create([
            'wallet_id'   => $request->wallet_id,
            'description' => $request->description,
            'category_id' => null,
            'amount'      => $request->amount,
            'type'        => 'pemasukan',
            'date'        => $request->date,
        ]);

        return response()->json([
            'message' => 'Pemasukan berhasil ditambahkan',
            'data'    => $pemasukan
        ], 201);
    }

    /**
     * Update the specified pemasukan.
     */
    public function update(Request $request, $id)
    {
        $pemasukan = Auth::user()->transactions()
            ->where('type', 'pemasukan')
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

        $pemasukan->update([
            'wallet_id'   => $request->wallet_id,
            'description' => $request->description,
            'category_id' => null,
            'amount'      => $request->amount,
            'date'        => $request->date,
        ]);

        return response()->json([
            'message' => 'Pemasukan berhasil diupdate',
            'data'    => $pemasukan
        ]);
    }

    /**
     * Remove the specified pemasukan.
     */
    public function destroy($id)
    {
        $pemasukan = Auth::user()->transactions()
            ->where('type', 'pemasukan')
            ->findOrFail($id);

        $pemasukan->delete();

        return response()->json([
            'message' => 'Pemasukan berhasil dihapus'
        ]);
    }

    /**
     * Show the specified pemasukan.
     */
    public function show($id)
    {
        $pemasukan = Auth::user()->transactions()
            ->where('type', 'pemasukan')
            ->findOrFail($id);

        return response()->json([
            'data' => $pemasukan
        ]);
    }
}
