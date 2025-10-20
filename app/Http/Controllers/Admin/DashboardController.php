<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CustomWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $walletService;

    public function __construct(CustomWalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function index()
    {
        $user = Auth::user();
        $userType = UserType::from($user->type);

        // Players are not allowed to access admin dashboard
        if ($userType === UserType::Player) {
            abort(403, 'Players are not authorized to access the admin dashboard.');
        }

        // Owner Dashboard
        if ($userType === UserType::Owner) {
            return $this->ownerDashboard();
        }

        // SystemWallet Dashboard
        if ($userType === UserType::SystemWallet) {
            return $this->systemWalletDashboard();
        }

        // Default fallback
        abort(403, 'Unauthorized access.');
    }

    private function ownerDashboard()
    {
        $user = Auth::user();
        
        // Get statistics
        $totalPlayers = User::where('type', UserType::Player->value)->count();
        $activePlayers = User::where('type', UserType::Player->value)
                            ->where('status', 1)
                            ->count();
        $totalBalance = User::where('type', UserType::Player->value)->sum('balance');
        
        // Get wallet statistics
        $walletStats = $this->walletService->getWalletStats();
        
        // Recent players
        $recentPlayers = User::where('type', UserType::Player->value)
                            ->latest()
                            ->take(10)
                            ->get();

        // Recent transactions (if you have CustomTransaction model)
        $recentTransactions = [];
        if (class_exists(\App\Models\CustomTransaction::class)) {
            $recentTransactions = \App\Models\CustomTransaction::with(['user', 'targetUser'])
                                ->latest()
                                ->take(10)
                                ->get();
        }

        return view('admin.dashboard.owner', compact(
            'user',
            'totalPlayers',
            'activePlayers',
            'totalBalance',
            'walletStats',
            'recentPlayers',
            'recentTransactions'
        ));
    }

    private function systemWalletDashboard()
    {
        $user = Auth::user();
        
        // Get system wallet statistics
        $walletStats = $this->walletService->getWalletStats();
        $systemBalance = $user->balance;
        
        // Recent transactions for system wallet
        $recentTransactions = [];
        if (class_exists(\App\Models\CustomTransaction::class)) {
            $recentTransactions = \App\Models\CustomTransaction::where('user_id', $user->id)
                                ->orWhere('target_user_id', $user->id)
                                ->with(['user', 'targetUser'])
                                ->latest()
                                ->take(20)
                                ->get();
        }

        return view('admin.dashboard.system-wallet', compact(
            'user',
            'systemBalance',
            'walletStats',
            'recentTransactions'
        ));
    }
}

