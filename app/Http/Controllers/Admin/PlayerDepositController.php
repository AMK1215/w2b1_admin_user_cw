<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DepositRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlayerDepositController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $deposits = DepositRequest::where('user_id', $user->id)
            ->with(['paymentType', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.player.my-deposits', compact('deposits'));
    }
}

