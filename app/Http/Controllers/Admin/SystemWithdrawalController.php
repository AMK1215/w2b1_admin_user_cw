<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SystemWithdrawalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $withdrawals = CustomTransaction::where('user_id', $user->id)
            ->where('type', 'withdraw')
            ->with(['user', 'targetUser'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $totalWithdrawals = CustomTransaction::where('user_id', $user->id)
            ->where('type', 'withdraw')
            ->sum('amount');

        return view('admin.system-wallet.withdrawals', compact('withdrawals', 'totalWithdrawals'));
    }
}

