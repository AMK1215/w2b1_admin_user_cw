<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index($id)
    {
        $user = User::with('paymentType')->findOrFail($id);
        
        // Check if user can view this profile
        if (Auth::id() != $id && !Auth::user()->hasRole('Owner') && !Auth::user()->hasRole('SystemWallet')) {
            abort(403, 'Unauthorized action.');
        }

        $paymentTypes = PaymentType::where('status', 1)->get();

        return view('admin.profile.index', compact('user', 'paymentTypes'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Check if user can update this profile
        if (Auth::id() != $id && !Auth::user()->hasRole('Owner')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'payment_type_id' => 'nullable|exists:payment_types,id',
            'account_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'profile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => 'nullable|min:6|confirmed',
        ]);

        $data = $request->only(['name', 'phone', 'email', 'payment_type_id', 'account_name', 'account_number']);

        // Handle profile image upload
        if ($request->hasFile('profile')) {
            // Delete old profile if exists
            if ($user->profile && Storage::disk('public')->exists($user->profile)) {
                Storage::disk('public')->delete($user->profile);
            }

            $data['profile'] = $request->file('profile')->store('profiles', 'public');
        }

        // Handle password change
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
            $data['is_changed_password'] = 1;
        }

        $user->update($data);

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }
}

