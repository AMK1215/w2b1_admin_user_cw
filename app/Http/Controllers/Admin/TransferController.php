<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TransactionName;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\TransferLog;
use App\Models\User;
use App\Services\CustomWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    protected $walletService;

    public function __construct(CustomWalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function index()
    {
        $user = Auth::user();
        
        // Get all players that belong to this owner
        // Note: agent_id is actually owner_id (Owner->Player relationship only)
        $players = User::where('type', UserType::Player->value)
            ->where('agent_id', $user->id)
            ->where('status', 1)
            ->get();

        // Get recent transfers
        $recentTransfers = TransferLog::where('from_user_id', $user->id)
            ->orWhere('to_user_id', $user->id)
            ->with(['fromUser', 'toUser'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.transfer.index', compact('players', 'recentTransfers'));
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'to_user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:500',
        ]);

        $fromUser = Auth::user();
        $toUser = User::findOrFail($request->to_user_id);
        $amount = (float) $request->amount;

        // Check if user has sufficient balance
        if (!$this->walletService->hasBalance($fromUser, $amount)) {
            return redirect()->back()->with('error', 'Insufficient balance!');
        }

        // Check if toUser is a player under this owner
        // Note: agent_id is actually owner_id (Owner->Player relationship only)
        if ($toUser->agent_id != $fromUser->id) {
            return redirect()->back()->with('error', 'You can only transfer to your own players!');
        }

        DB::beginTransaction();
        try {
            // Perform transfer with description
            $description = $request->description ?? "Transfer from {$fromUser->user_name} to {$toUser->user_name}";
            
            $transferred = $this->walletService->transfer(
                $fromUser,
                $toUser,
                $amount,
                TransactionName::CreditTransfer,
                ['description' => $description]
            );

            if (!$transferred) {
                throw new \Exception('Transfer failed');
            }

            DB::commit();
            return redirect()->back()->with('success', 'Transfer completed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Transfer failed: ' . $e->getMessage());
        }
    }
}

