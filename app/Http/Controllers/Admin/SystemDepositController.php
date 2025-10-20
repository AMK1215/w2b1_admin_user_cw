<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SystemDepositController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $deposits = CustomTransaction::where('user_id', $user->id)
            ->where('type', 'deposit')
            ->with(['user', 'targetUser'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $totalDeposits = CustomTransaction::where('user_id', $user->id)
            ->where('type', 'deposit')
            ->sum('amount');

        return view('admin.system-wallet.deposits', compact('deposits', 'totalDeposits'));
    }
}

