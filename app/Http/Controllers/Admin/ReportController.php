<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\CustomTransaction;
use App\Models\DepositRequest;
use App\Models\User;
use App\Models\WithDrawRequest;
use App\Services\CustomWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    protected $walletService;

    public function __construct(CustomWalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        // Note: agent_id field is actually owner_id (Owner->Player relationship only, no agent system)
        
        // Player statistics - count players under this owner
        $totalPlayers = User::where('type', UserType::Player->value)
            ->where('agent_id', $user->id) // agent_id = owner_id
            ->count();

        $activePlayers = User::where('type', UserType::Player->value)
            ->where('agent_id', $user->id) // agent_id = owner_id
            ->where('status', 1)
            ->count();

        // Transaction statistics - deposits and withdrawals by owner's players
        $totalDeposits = DepositRequest::where('agent_id', $user->id) // agent_id = owner_id
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 1)
            ->sum('amount');

        $totalWithdrawals = WithDrawRequest::where('agent_id', $user->id) // agent_id = owner_id
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 1)
            ->sum('amount');

        // Total balance of all players under this owner
        $totalPlayerBalance = User::where('type', UserType::Player->value)
            ->where('agent_id', $user->id) // agent_id = owner_id
            ->sum('balance');

        // Recent transactions for players under this owner
        $recentTransactions = CustomTransaction::whereHas('user', function($query) use ($user) {
                $query->where('agent_id', $user->id); // agent_id = owner_id
            })
            ->orWhereHas('targetUser', function($query) use ($user) {
                $query->where('agent_id', $user->id); // agent_id = owner_id
            })
            ->with(['user', 'targetUser'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.reports.index', compact(
            'totalPlayers',
            'activePlayers',
            'totalDeposits',
            'totalWithdrawals',
            'totalPlayerBalance',
            'recentTransactions',
            'dateFrom',
            'dateTo'
        ));
    }

    public function systemReports(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        // System-wide statistics
        $walletStats = $this->walletService->getWalletStats();

        // Total transactions by type
        $transactionStats = CustomTransaction::select('type', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('type')
            ->get();

        // Recent system transactions
        $recentTransactions = CustomTransaction::with(['user', 'targetUser'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.reports.system', compact(
            'walletStats',
            'transactionStats',
            'recentTransactions',
            'dateFrom',
            'dateTo'
        ));
    }
}

