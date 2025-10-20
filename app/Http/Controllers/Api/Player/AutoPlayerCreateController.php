<?php

namespace App\Http\Controllers\Api\Player;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AutoPlayerCreateController extends Controller
{

    private const PLAYER_ROLE = 2;

    public function register(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'user_name' => 'required|string|max:255|unique:users',
            'phone' => 'nullable|string|max:255',
            'password' => 'required|string|min:6|confirmed',
            'agent_id' => 'nullable|exists:users,id',
            //'status' => 'required|integer|in:1',
            'is_changed_password' => 'required|integer|in:1',
            'type' => 'required|integer|in:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }


        
        try {
            // Create the guest user
            $user = User::create([
                'name' => $request->name,
                'user_name' => $request->user_name,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'agent_id' => 1,
                'status' => 1,
                'is_changed_password' => $request->is_changed_password,
                'type' => $request->type,
            ]);

            $user->roles()->sync(self::PLAYER_ROLE);


            // Generate token
            $token = $user->createToken($user->user_name)->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Guest account created successfully',
                'data' => [
                    'user' => $user,
                    'token' => $token
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create guest account',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
}