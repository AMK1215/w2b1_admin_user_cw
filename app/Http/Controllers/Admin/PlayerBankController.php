<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlayerBankController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $paymentTypes = PaymentType::where('status', 1)->get();

        return view('admin.player.my-banks', compact('user', 'paymentTypes'));
    }
}

