<?php

namespace Database\Seeders;

use App\Enums\TransactionName;
use App\Enums\UserType;
use App\Models\User;
use App\Services\CustomWalletService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $walletService = new CustomWalletService();

        // Create Owner
        $owner = $this->createUser(UserType::Owner, 'Owner', 'owner', '09123456789');
        $walletService->deposit($owner, 10 * 100_000, TransactionName::CapitalDeposit);

        // Create SystemWallet
        $systemWallet = $this->createUser(UserType::SystemWallet, 'System Wallet', 'system', '09999999999');
        $walletService->deposit($systemWallet, 5 * 100_000, TransactionName::CapitalDeposit);

        // Create Players under Owner
        $player_1 = $this->createUser(UserType::Player, 'Player 1', 'P-11111', '09511111111', $owner->id);
        $walletService->transfer($owner, $player_1, 30_000, TransactionName::CreditTransfer);
        
        $player_2 = $this->createUser(UserType::Player, 'Player 2', 'P-22222', '09522222222', $owner->id);
        $walletService->transfer($owner, $player_2, 30_000, TransactionName::CreditTransfer);
        
        $player_3 = $this->createUser(UserType::Player, 'Player 3', 'P-33333', '09533333333', $owner->id);
        $walletService->transfer($owner, $player_3, 30_000, TransactionName::CreditTransfer);
        
        $player_4 = $this->createUser(UserType::Player, 'Player 4', 'P-44444', '09544444444', $owner->id);
        $walletService->transfer($owner, $player_4, 30_000, TransactionName::CreditTransfer);
        
        $player_5 = $this->createUser(UserType::Player, 'Player 5', 'P-55555', '09555555555', $owner->id);
        $walletService->transfer($owner, $player_5, 30_000, TransactionName::CreditTransfer);
    }

    private function createUser(UserType $type, $name, $user_name, $phone, $parent_id = null)
    {
        return User::create([
            'name' => $name,
            'user_name' => $user_name,
            'phone' => $phone,
            'password' => Hash::make('shwedragon'),
            'agent_id' => $parent_id,
            'status' => 1,
            'is_changed_password' => 1,
            'type' => $type->value,
        ]);
    }
}
