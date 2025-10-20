<?php

namespace App\Http\Controllers\Api\V1\Game;

use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CustomWalletService;
use App\Services\BuffaloGameService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BuffaloGameController extends Controller
{
    protected CustomWalletService $customWalletService;

    public function __construct(CustomWalletService $customWalletService)
    {
        $this->customWalletService = $customWalletService;
    }

    /**
     * Buffalo Game - Get User Balance
     */
    public function getUserBalance(Request $request)
    {
        $request->validate([
            'uid' => 'required|string|max:50',
            'token' => 'required|string',
        ]);

        $uid = $request->uid;
        $token = $request->token;

        // ✅ Verify token (custom logic)
        if (!BuffaloGameService::verifyToken($uid, $token)) {
            return response()->json([
                'code' => 0,
                'msg' => 'Invalid token',
            ]);
        }

        // ✅ Extract user_name from uid (site prefix format: "gam-username")
        // Site: gamestar77.online -> prefix: "gam"
        $userName = BuffaloGameService::extractUserNameFromUid($uid);

        // ✅ Lookup user by user_name (your system's identifier)
        $user = User::where('user_name', $userName)->first();
        
        if (!$user) {
            return response()->json([
                'code' => 0,
                'msg' => 'User not found',
            ]);
        }

        // ✅ Get balance using your CustomWalletService
        $balance = $this->customWalletService->getBalance($user);

        // ✅ Return balance directly (no conversion needed - provider confirmed)
        return response()->json([
            'code' => 1,
            'msg' => 'Success',
            'balance' => $balance,
        ]);
    }

    /**
     * Buffalo Game - Change Balance (Bet/Win)
     */
    public function changeBalance(Request $request)
    {
        $request->validate([
            'uid' => 'required|string|max:50',
            'token' => 'required|string',
            'changemoney' => 'required|integer',
            'bet' => 'required|integer',
            'win' => 'required|integer', // Provider confirmed: should be integer
            'gameId' => 'required|integer', // Provider confirmed: should be integer
        ]);

        $uid = $request->uid;
        $token = $request->token;

        // ✅ Verify token
        if (!BuffaloGameService::verifyToken($uid, $token)) {
            return response()->json([
                'code' => 0,
                'msg' => 'Invalid token',
            ]);
        }

        // ✅ Extract user_name from uid (site prefix format: "gam-username")
        $userName = BuffaloGameService::extractUserNameFromUid($uid);

        // ✅ Lookup user by user_name
        $user = User::where('user_name', $userName)->first();
        
        if (!$user) {
            return response()->json([
                'code' => 0,
                'msg' => 'User not found',
            ]);
        }

        // ✅ Use amounts directly (no conversion needed - provider confirmed)
        $changeAmount = $request->changemoney; // Provider confirmed: direct values (can be positive/negative)
        $betAmount = abs($request->bet);
        $winAmount = $request->win; // Provider confirmed: integer (can be positive/negative)

        Log::info('Buffalo Game Transaction', [
            'user_name' => $user->user_name,
            'user_id' => $user->id,
            'change_amount' => $changeAmount,
            'bet_amount' => $betAmount,
            'win_amount' => $winAmount,
            'game_id' => $request->gameId,
            'original_request' => $request->all()
        ]);

        try {
            // ✅ Handle different transaction types
            if ($changeAmount > 0) {
                // Win/Deposit transaction
                $success = $this->customWalletService->deposit(
                    $user,
                    $changeAmount,
                    TransactionName::GameWin,
                    [
                        'buffalo_game_id' => $request->gameId, // Provider confirmed: integer
                        'bet_amount' => $betAmount,
                        'win_amount' => $winAmount,
                        'provider' => 'buffalo',
                        'transaction_type' => 'game_win'
                    ]
                );
            } else {
                // Loss/Withdraw transaction
                $success = $this->customWalletService->withdraw(
                    $user,
                    abs($changeAmount),
                    TransactionName::GameLoss,
                    [
                        'buffalo_game_id' => $request->gameId, // Provider confirmed: integer
                        'bet_amount' => $betAmount,
                        'win_amount' => $winAmount,
                        'provider' => 'buffalo',
                        'transaction_type' => 'game_loss'
                    ]
                );
            }

            if (!$success) {
                return response()->json([
                    'code' => 0,
                    'msg' => 'Transaction failed',
                ]);
            }

            // ✅ Refresh user model to get updated balance
            $user->refresh();

            // ✅ Log the bet data for reporting
            $this->logBuffaloBet($user, $request->all());

            return response()->json([
                'code' => 1,
                'msg' => 'Balance updated successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Buffalo Game Transaction Error', [
                'user_name' => $user->user_name,
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'code' => 0,
                'msg' => 'Transaction failed: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Generate Buffalo game authentication data for frontend
     */
    public function generateGameAuth(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'code' => 0,
                'msg' => 'User not authenticated',
            ]);
        }

        $auth = BuffaloGameService::generateBuffaloAuth($user);
        $availableRooms = BuffaloGameService::getAvailableRooms($user);
        $roomConfig = BuffaloGameService::getRoomConfig();

        return response()->json([
            'code' => 1,
            'msg' => 'Success',
            'data' => [
                'auth' => $auth,
                'available_rooms' => $availableRooms,
                'all_rooms' => $roomConfig,
                'user_balance' => $user->balance,
            ],
        ]);
    }

    /**
     * Generate Buffalo game URL for direct launch
     */
    public function generateGameUrl(Request $request)
    {
        $request->validate([
            'room_id' => 'required|integer|min:1|max:4',
            'lobby_url' => 'nullable|url',
        ]);

        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'code' => 0,
                'msg' => 'User not authenticated',
            ]);
        }

        $roomId = $request->room_id;
        $lobbyUrl = $request->lobby_url ?: config('app.url');

        // Check if user has sufficient balance for the room
        $availableRooms = BuffaloGameService::getAvailableRooms($user);
        
        if (!isset($availableRooms[$roomId])) {
            return response()->json([
                'code' => 0,
                'msg' => 'Insufficient balance for selected room',
            ]);
        }

        $gameUrl = BuffaloGameService::generateGameUrl($user, $roomId, $lobbyUrl);

        return response()->json([
            'code' => 1,
            'msg' => 'Success',
            'data' => [
                'game_url' => $gameUrl,
                'room_info' => $availableRooms[$roomId],
            ],
        ]);
    }

    /**
     * Log Buffalo bet for reporting
     */
    private function logBuffaloBet(User $user, array $requestData): void
    {
        try {
            // ✅ Use LogBuffaloBet model with correct fields
            \App\Models\LogBuffaloBet::create([
                'member_account' => $user->user_name,
                'player_id' => $user->id,
                'player_agent_id' => $user->agent_id,
                'buffalo_game_id' => $requestData['gameId'], // Provider confirmed: integer
                'request_time' => now(),
                'bet_amount' => abs($requestData['bet']), // Provider confirmed: direct values
                'win_amount' => $requestData['win'], // Provider confirmed: integer (can be positive/negative)
                'payload' => $requestData, // Store full request data
                'game_name' => 'Buffalo Slot Game',
                'status' => 'completed',
                'before_balance' => $user->balance - $requestData['changemoney'],
                'balance' => $user->balance,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to log Buffalo bet', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'request_data' => $requestData
            ]);
        }
    }

    /**
     * Buffalo Game - Launch Game (Frontend Integration)
     * Compatible with existing frontend LaunchGame hook
     */
    public function launchGame(Request $request)
    {
        $request->validate([
            'type_id' => 'required|integer',
            'provider_id' => 'required|integer',
            'game_id' => 'required|integer',
            'room_id' => 'nullable|integer|min:1|max:4', // Optional room selection
        ]);

        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'code' => 0,
                'msg' => 'User not authenticated',
            ], 401);
        }

        try {
            // Check if this is a Buffalo game request
            if ($request->provider_id === 23) { // Assuming 23 is Buffalo provider ID
                // Generate Buffalo game authentication
                $auth = BuffaloGameService::generateBuffaloAuth($user);
                
                // Get room configuration
                $roomId = $request->room_id ?? 1; // Default to room 1
                $availableRooms = BuffaloGameService::getAvailableRooms($user);
                
                // Check if requested room is available for user's balance
                if (!isset($availableRooms[$roomId])) {
                    return response()->json([
                        'code' => 0,
                        'msg' => 'Room not available for your balance level',
                    ]);
                }
                
                $roomConfig = $availableRooms[$roomId];
                
                // Generate Buffalo game URL (Production - HTTP as per provider format)
                $lobbyUrl = 'https://africanbuffalo.vip';
                $gameUrl = BuffaloGameService::generateGameUrl($user, $roomId, $lobbyUrl);
                
                // Add UID and token to the URL (exact provider format)
                $gameUrl .= '&uid=' . $auth['uid'] . '&token=' . $auth['token'];
                
                Log::info('Buffalo Game Launch', [
                    'user_id' => $user->id,
                    'user_name' => $user->user_name,
                    'room_id' => $roomId,
                    'game_url' => $gameUrl,
                    'auth_data' => $auth
                ]);
                
                return response()->json([
                    'code' => 1,
                    'msg' => 'Game launched successfully',
                    'Url' => $gameUrl, // Compatible with existing frontend
                    'game_url' => $gameUrl, // HTTP URL (exact provider format)
                    'room_info' => $roomConfig,
                    'user_balance' => $user->balance,
                ]);
            }
            
            // For non-Buffalo games, you can add other provider logic here
            return response()->json([
                'code' => 0,
                'msg' => 'Game provider not supported',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Buffalo Game Launch Error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'Failed to launch game',
            ]);
        }
    }
}