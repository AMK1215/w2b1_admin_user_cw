<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WithDrawRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlayerWithdrawalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $withdrawals = WithDrawRequest::where('user_id', $user->id)
            ->with(['paymentType', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.player.my-withdrawals', compact('withdrawals'));
    }
}

