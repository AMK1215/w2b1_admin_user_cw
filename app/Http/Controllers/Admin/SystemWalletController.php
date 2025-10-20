<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\CustomTransaction;
use App\Models\User;
use App\Services\CustomWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SystemWalletController extends Controller
{
    protected $walletService;

    public function __construct(CustomWalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function index()
    {
        // Get system wallet user
        $systemWallet = User::where('type', UserType::SystemWallet->value)->first();

        if (!$systemWallet) {
            return redirect()->back()->with('error', 'System wallet not found.');
        }

        // Get transactions
        $transactions = CustomTransaction::where(function($query) use ($systemWallet) {
                $query->where('user_id', $systemWallet->id)
                      ->orWhere('target_user_id', $systemWallet->id);
            })
            ->with(['user', 'targetUser'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $balance = $systemWallet->balance;
        $totalTransactions = CustomTransaction::where('user_id', $systemWallet->id)
            ->orWhere('target_user_id', $systemWallet->id)
            ->count();

        return view('admin.system-wallet.index', compact('systemWallet', 'transactions', 'balance', 'totalTransactions'));
    }

    public function dashboard()
    {
        $user = Auth::user();
        
        if ($user->type != UserType::SystemWallet->value) {
            abort(403, 'Unauthorized access.');
        }

        $walletStats = $this->walletService->getWalletStats();
        $systemBalance = $user->balance;
        
        $recentTransactions = CustomTransaction::where('user_id', $user->id)
            ->orWhere('target_user_id', $user->id)
            ->with(['user', 'targetUser'])
            ->latest()
            ->take(20)
            ->get();

        return view('admin.system-wallet.dashboard', compact('user', 'systemBalance', 'walletStats', 'recentTransactions'));
    }
}

