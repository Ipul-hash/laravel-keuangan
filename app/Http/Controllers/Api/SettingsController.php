<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Wallet;
use App\Models\SavingGoal;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * Get all user settings
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        
        // Get user settings from users table
        $notifications = $user->notifications_enabled ?? true;
        $abnormalThreshold = $user->abnormal_threshold ?? 500000;
        
        // Get active target (status = 'ongoing' dan paling recent)
        $target = SavingGoal::where('user_id', $user->id)
            ->where('status', 'ongoing')
            ->orderBy('created_at', 'desc')
            ->with('wallet')
            ->first();
        
        // Get all wallets with active status
        $wallets = Wallet::where('user_id', $user->id)
            ->orderByRaw('CASE WHEN is_active = 1 THEN 0 ELSE 1 END') // Active first
            ->orderBy('name')
            ->get()
            ->map(function ($wallet) {
                return [
                    'id' => $wallet->id,
                    'name' => $wallet->name,
                    'note' => $wallet->description,
                    'active' => $wallet->is_active ?? false
                ];
            });
        
        return response()->json([
            'target' => $target ? [
                'id' => $target->id,
                'name' => $target->title,
                'amount' => $target->target_amount,
                'current_amount' => $target->current_amount,
                'deadline' => $target->deadline,
                'active' => $target->status === 'ongoing'
            ] : null,
            'notifications' => $notifications,
            'abnormal_threshold' => $abnormalThreshold,
            'atms' => $wallets // Frontend expects "atms" but we use wallets
        ]);
    }

    /**
     * Create new wallet
     */
    public function createWallet(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500'
        ]);

        $user = Auth::user();
        
        // Check if this is first wallet, make it active
        $isFirstWallet = !Wallet::where('user_id', $user->id)->exists();
        
        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $isFirstWallet
        ]);

        return response()->json([
            'message' => 'Wallet berhasil ditambahkan',
            'wallet' => [
                'id' => $wallet->id,
                'name' => $wallet->name,
                'note' => $wallet->description,
                'active' => $wallet->is_active ?? false
            ]
        ]);
    }

    /**
     * Update wallet
     */
    public function updateWallet(Request $request, $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500'
        ]);

        $wallet = Wallet::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $wallet->update([
            'name' => $request->name,
            'description' => $request->description
        ]);

        return response()->json([
            'message' => 'Wallet berhasil diupdate',
            'wallet' => [
                'id' => $wallet->id,
                'name' => $wallet->name,
                'note' => $wallet->description,
                'active' => $wallet->is_active ?? false
            ]
        ]);
    }

    /**
     * Delete wallet with active wallet handling
     */
    public function deleteWallet($id): JsonResponse
    {
        $user = Auth::user();
        $wallet = Wallet::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $isActive = $wallet->is_active ?? false;
        
        DB::beginTransaction();
        
        try {
            // Update transactions to remove wallet_id reference (set to null)
            Transaction::where('wallet_id', $id)->update(['wallet_id' => null]);
            
            // Update saving goals to remove wallet reference
            SavingGoal::where('wallet_id', $id)->update(['wallet_id' => null]);
            
            // Delete wallet
            $wallet->delete();
            
            $response = ['message' => 'Wallet berhasil dihapus'];
            
            // If deleted wallet was active, check if there are other wallets
            if ($isActive) {
                $remainingWallets = Wallet::where('user_id', $user->id)->get();
                
                if ($remainingWallets->count() > 0) {
                    // Return list of wallets for user to choose
                    $response['needs_active_selection'] = true;
                    $response['available_atms'] = $remainingWallets->map(function ($wallet) {
                        return [
                            'id' => $wallet->id,
                            'name' => $wallet->name,
                            'note' => $wallet->description
                        ];
                    });
                } else {
                    $response['no_atms_left'] = true;
                }
            }
            
            DB::commit();
            return response()->json($response);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menghapus wallet'], 500);
        }
    }

    /**
     * Set wallet as active
     */
    public function setActiveWallet($id): JsonResponse
    {
        $user = Auth::user();
        
        // Verify wallet belongs to user
        $wallet = Wallet::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        // Set all wallets inactive first, then activate this one
        Wallet::where('user_id', $user->id)->update(['is_active' => false]);
        $wallet->update(['is_active' => true]);

        return response()->json(['message' => 'Wallet berhasil diaktifkan']);
    }

    /**
     * Save/Update savings target
     */
    public function saveTarget(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'deadline' => 'nullable|date|after:today'
        ]);

        $user = Auth::user();

        // Get active wallet
        $activeWallet = Wallet::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (!$activeWallet) {
            return response()->json([
                'message' => 'Tidak ada wallet aktif. Silakan aktifkan wallet terlebih dahulu.'
            ], 422);
        }

        DB::beginTransaction();
        
        try {
            // Set existing ongoing targets to cancelled
            SavingGoal::where('user_id', Auth::id())
    ->where('status', 'ongoing')
    ->update([
        'status' => 'cancelled',
        'is_active' => false // ← opsional, tapi lebih rapi
    ]);

            // Create new target
            $target = SavingGoal::create([
                'user_id' => $user->id,
                'wallet_id' => $activeWallet->id,
                'title' => $request->name,
                'target_amount' => $request->amount,
                'current_amount' => 0,
                'deadline' => $request->deadline,
                'status' => 'ongoing',
                'is_active' => true, // 👈 INI YANG DITAMBAHKAN!
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Target tabungan berhasil disimpan',
                'target' => [
                    'id' => $target->id,
                    'name' => $target->title,
                    'amount' => $target->target_amount,
                    'current_amount' => $target->current_amount,
                    'deadline' => $target->deadline,
                    'active' => $target->status === 'ongoing'
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menyimpan target'], 500);
        }
    }

    /**
     * Delete savings target
     */
    public function deleteTarget(): JsonResponse
    {
        SavingGoal::where('user_id', Auth::id())
            ->where('status', 'ongoing')
            ->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Target tabungan berhasil dihapus']);
    }

    /**
     * Update notifications setting
     */
    public function updateNotifications(Request $request): JsonResponse
    {
        $request->validate([
            'notifications' => 'required|boolean'
        ]);

        $user = Auth::user();
        $user->update([
            'notifications_enabled' => $request->notifications
        ]);

        return response()->json(['message' => 'Pengaturan notifikasi berhasil diupdate']);
    }

    /**
     * Update abnormal threshold
     */
    public function updateAbnormalThreshold(Request $request): JsonResponse
    {
        $request->validate([
            'threshold' => 'required|numeric|min:0'
        ]);

        $user = Auth::user();
        $user->update([
            'abnormal_threshold' => $request->threshold
        ]);

        return response()->json(['message' => 'Threshold pengeluaran abnormal berhasil diupdate']);
    }

    /**
     * Check if expense is abnormal (untuk dipanggil saat input transaksi)
     */
    public function checkAbnormalExpense($amount): bool
    {
        $user = Auth::user();
        
        if (!$user->notifications_enabled) {
            return false;
        }
        
        return $amount > ($user->abnormal_threshold ?? 500000);
    }

    /**
     * Get active wallet for transaction input
     */
    public function getActiveWallet()
    {
        return Wallet::where('user_id', Auth::id())
            ->where('is_active', true)
            ->first();
    }

    public function active()
{
    $wallet = Auth::user()->wallets()->where('is_active', true)->first();

    if (!$wallet) {
        return response()->json(['message' => 'Tidak ada wallet aktif'], 404);
    }

    return response()->json($wallet);
}

}