<?php

namespace Database\Seeders;

use App\Models\Admin\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Owner permissions
            [
                'title' => 'owner_access',
                'group' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'owner_index',
                'group' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'owner_create',
                'group' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'owner_edit',
                'group' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'owner_delete',
                'group' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'owner_change_password',
                'group' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Player permissions
            [
                'title' => 'player_access',
                'group' => 'player',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'player_index',
                'group' => 'player',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'player_create',
                'group' => 'player',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'player_edit',
                'group' => 'player',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'player_delete',
                'group' => 'player',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'player_view',
                'group' => 'player',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'player_change_password',
                'group' => 'player',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // SystemWallet permissions
            [
                'title' => 'system_wallet_access',
                'group' => 'systemwallet',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'system_wallet_index',
                'group' => 'systemwallet',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Common/Shared permissions (used by multiple roles)
            [
                'title' => 'transfer_log',
                'group' => 'common',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'make_transfer',
                'group' => 'common',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'bank',
                'group' => 'common',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'withdraw',
                'group' => 'common',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'deposit',
                'group' => 'common',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'contact',
                'group' => 'common',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'report_check',
                'group' => 'common',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'game_type_access',
                'group' => 'common',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'game_list_access',
                'group' => 'common',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'provider_access',
                'group' => 'common',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'provider_create',
                'group' => 'common',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'provider_edit',
                'group' => 'common',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'provider_delete',
                'group' => 'common',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'provider_index',
                'group' => 'common',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'banner_access',
                'group' => 'common',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'promotion_access',
                'group' => 'common',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        Permission::insert($permissions);
    }
}

