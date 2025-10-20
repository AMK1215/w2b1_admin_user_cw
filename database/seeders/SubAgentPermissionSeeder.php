<?php

namespace Database\Seeders;

use App\Models\Admin\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class SubAgentPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder is for additional SubAgent-specific permissions
     * that may need to be added after the main permissions seeder.
     */
    public function run(): void
    {
        // Check if subagent permissions already exist
        $existingPermissions = Permission::where('group', 'subagent')->pluck('title')->toArray();
        
        $additionalPermissions = [
            // Add any additional SubAgent-specific permissions here
            // These will be inserted only if they don't already exist
        ];

        $permissionsToInsert = [];
        foreach ($additionalPermissions as $permission) {
            if (!in_array($permission['title'], $existingPermissions)) {
                $permission['created_at'] = now();
                $permission['updated_at'] = now();
                $permissionsToInsert[] = $permission;
            }
        }

        if (!empty($permissionsToInsert)) {
            Permission::insert($permissionsToInsert);
            Log::info('SubAgentPermissionSeeder: Inserted ' . count($permissionsToInsert) . ' additional permissions');
        } else {
            Log::info('SubAgentPermissionSeeder: No additional permissions to insert');
        }
    }
}

