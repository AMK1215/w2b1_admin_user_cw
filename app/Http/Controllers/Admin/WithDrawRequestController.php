<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WithDrawRequest;
use App\Services\CustomWalletService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WithDrawRequestController extends Controller
{
    public function index(Request $request)
    {
        // Check permissions - Owner only
        if (! Auth::user()->hasPermission('withdraw')) {
            abort(403, 'You do not have permission to access withdrawal requests.');
        }

        $user = Auth::user();
        // Note: agent_id is actually owner_id (Owner->Player relationship only)
        $owner = $user;

        $startDate = $request->start_date ?? Carbon::today()->startOfDay()->toDateString();
        $endDate = $request->end_date ?? Carbon::today()->endOfDay()->toDateString();

        $withdraws = WithDrawRequest::with(['user', 'paymentType'])
            ->where('agent_id', $owner->id) // agent_id = owner_id
            ->when($request->filled('status') && $request->input('status') !== 'all', function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->orderBy('id', 'desc')
            ->get();

        $totalWithdraws = $withdraws->sum('amount');

        return view('admin.withdraw_request.index', compact('withdraws', 'totalWithdraws'));
    }

    public function statusChangeIndex(Request $request, WithDrawRequest $withdraw)
    {
        // Check permissions
        if (! Auth::user()->hasPermission('withdraw')) {
            abort(403, 'You do not have permission to process withdrawals.');
        }

        Log::info('Withdraw status change started', [
            'withdraw_id' => $withdraw->id,
            'request_status' => $request->status,
            'request_player' => $request->player,
            'user_id' => Auth::id(),
        ]);

        $user = Auth::user();
        $owner = $user; // No agent system
        $player = User::find($request->player);

        Log::info('User and owner info', [
            'user_id' => $user->id,
            'user_name' => $user->user_name,
            'owner_id' => $owner ? $owner->id : null,
            'owner_name' => $owner ? $owner->user_name : null,
            'player_id' => $player ? $player->id : null,
            'player_name' => $player ? $player->user_name : null,
            'player_balance' => $player ? $player->balance : null,
        ]);

        if ($request->status == 1 && $player->balance < $request->amount) {
            Log::warning('Insufficient balance for withdraw', [
                'player_balance' => $player->balance,
                'request_amount' => $request->amount,
                'withdraw_id' => $withdraw->id,
            ]);

            return redirect()->back()->with('error', 'Insufficient Balance!');
        }

        $note = 'Withdraw request approved by '.$user->user_name.' on '.Carbon::now()->timezone('Asia/Yangon')->format('d-m-Y H:i:s');

        Log::info('Updating withdraw request', [
            'withdraw_id' => $withdraw->id,
            'status' => $request->status,
            'note' => $note,
        ]);

        $withdraw->update([
            'status' => $request->status,
            'note' => $note,
        ]);

        if ($request->status == 1) {
            Log::info('Processing withdraw approval', [
                'withdraw_id' => $withdraw->id,
                'amount' => $request->amount,
                'player_old_balance' => $player->balance,
            ]);

            $old_balance = $player->balance;

            try {
                $transferResult = app(CustomWalletService::class)->transfer($player, $owner, $request->amount,
                    TransactionName::Withdraw, [
                        'note' => 'Withdraw request approved',
                        'approved_by' => $user->user_name,
                        'withdraw_id' => $withdraw->id,
                    ]);
                
                if (!$transferResult) {
                    throw new \Exception('Transfer failed');
                }

                Log::info('Wallet transfer completed successfully', [
                    'withdraw_id' => $withdraw->id,
                    'transfer_amount' => $request->amount,
                    'player_old_balance' => $old_balance,
                    'player_new_balance' => $old_balance - $request->amount,
                ]);
            } catch (\Exception $e) {
                Log::error('Wallet transfer failed', [
                    'withdraw_id' => $withdraw->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }

            try {
                \App\Models\TransferLog::create([
                    'from_user_id' => $player->id,
                    'to_user_id' => $owner->id,
                    'amount' => $request->amount,
                    'type' => 'withdraw',
                    'description' => 'Withdraw request '.$withdraw->id.' approved by '.$user->user_name,
                    'meta' => [
                        'withdraw_request_id' => $withdraw->id,
                        'player_old_balance' => $old_balance,
                        'player_new_balance' => $old_balance - $request->amount,
                    ],
                ]);

                Log::info('Transfer log created successfully', [
                    'withdraw_id' => $withdraw->id,
                    'transfer_log_created' => true,
                ]);
            } catch (\Exception $e) {
                Log::error('Transfer log creation failed', [
                    'withdraw_id' => $withdraw->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
        }

        Log::info('Withdraw status change completed successfully', [
            'withdraw_id' => $withdraw->id,
            'final_status' => $request->status,
        ]);

        return redirect()->route('admin.withdrawals.index')->with('success', 'Withdraw status updated successfully!');
    }

    public function statusChangeReject(Request $request, WithDrawRequest $withdraw)
    {
        // Check permissions
        if (! Auth::user()->hasPermission('withdraw')) {
            abort(403, 'You do not have permission to process withdrawals.');
        }

        $request->validate([
            'status' => 'required|in:0,1,2',
        ]);

        $user = Auth::user();
        $owner = $user; // No agent system

        try {
            $note = 'Withdraw request rejected by '.$user->user_name.' on '.Carbon::now()->timezone('Asia/Yangon')->format('d-m-Y H:i:s');

            $withdraw->update([
                'status' => $request->status,
                'note' => $note,
            ]);

            \App\Models\TransferLog::create([
                'from_user_id' => $withdraw->user_id,
                'to_user_id' => $owner->id,
                'amount' => $withdraw->amount,
                'type' => 'withdraw-reject',
                'description' => 'Withdraw request '.$withdraw->id.' rejected by '.$user->user_name,
                'meta' => [
                    'withdraw_request_id' => $withdraw->id,
                    'status' => 'rejected',
                ],
            ]);

            return redirect()->route('admin.withdrawals.index')->with('success', 'Withdraw status updated successfully!');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // log withdraw request
    public function WithdrawShowLog(WithDrawRequest $withdraw)
    {
        // Check permissions
        if (! Auth::user()->hasPermission('withdraw')) {
            abort(403, 'You do not have permission to view withdrawal logs.');
        }

        $user = Auth::user();
        $owner = $user; // No agent system

        // Check if user has permission to handle this withdrawal
        // Note: agent_id is actually owner_id
        if ($withdraw->agent_id !== $owner->id) {
            return redirect()->back()->with('error', 'You do not have permission to handle this withdrawal request!');
        }

        return view('admin.withdraw_request.view', ['withdraw' => $withdraw]);
    }
}
