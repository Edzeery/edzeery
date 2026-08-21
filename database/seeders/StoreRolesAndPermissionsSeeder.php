<?php

namespace Database\Seeders;

use App\Enums\Platform\UserRoleEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Enums\Store\StoreStatusEnum;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Support\StoreRoles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class StoreRolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        try {
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            // =========================
            // Step 1: Create Store Permissions
            // =========================
            foreach (\App\Enums\Store\StorePermissionEnum::cases() as $permissionEnum) {
                Permission::firstOrCreate([
                    'name'       => $permissionEnum->value,
                    'guard_name' => 'merchant',
                ]);
            }

            // =========================
            // Step 2: Create Store Roles & Assign Permissions
            // =========================
            foreach (StoreRoleEnum::cases() as $roleEnum) {
                $role = Role::firstOrCreate([
                    'name'       => $roleEnum->value,
                    'guard_name' => 'merchant',
                ]);

                $permissions = StoreRoles::permissions($roleEnum);

                $permissionObjects = Permission::whereIn('name', $permissions)
                    ->where('guard_name', 'merchant')
                    ->get();

                if ($permissionObjects->isNotEmpty()) {
                    $role->syncPermissions($permissionObjects);
                }

                $this->command?->info("  Role [{$roleEnum->value}] → {$permissionObjects->count()} permissions synced.");
            }

            // =========================
            // Step 3: Create Merchant User + Default Store (OWNER)
            // =========================
            $merchantUser = $this->createUser(
                'merchant@edzeery.com',
                'Merchant Owner',
                UserRoleEnum::MERCHANT,
                StoreRoleEnum::OWNER,
            );

            $store = Store::firstOrCreate(
                ['slug' => 'default-store'],
                [
                    'user_id' => $merchantUser->id,
                    'name'    => 'Default Merchant Store',
                    'status'  => StoreStatusEnum::ACTIVE,
                ]
            );

            $this->ensureMembership($store, $merchantUser, $store->user_id, StoreRoleEnum::OWNER);

            // =========================
            // Step 4: Create demo team members (one per role)
            // =========================
            $demoMembers = [
                ['email' => 'admin@edzeery.com',    'name' => 'Admin User',    'role' => StoreRoleEnum::ADMIN],
                ['email' => 'manager@edzeery.com',  'name' => 'Manager User',  'role' => StoreRoleEnum::MANAGER],
                ['email' => 'staff@edzeery.com',    'name' => 'Staff User',    'role' => StoreRoleEnum::STAFF],
            ];

            foreach ($demoMembers as $member) {
                $user = $this->createUser(
                    $member['email'],
                    $member['name'],
                    UserRoleEnum::MERCHANT,
                    $member['role'],
                );

                $this->ensureMembership($store, $user, $merchantUser->id, $member['role']);
            }

            $this->command?->info('Store Roles & Permissions seeded successfully.');
        } catch (\Exception $e) {
            $this->command?->error("StoreRolesAndPermissionsSeeder failed: {$e->getMessage()}");
        }
    }

    private function createUser(
        string $email,
        string $name,
        UserRoleEnum $platformRole,
        StoreRoleEnum $storeRole,
    ): User {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'     => $name,
                'password' => Hash::make('password'),
            ]
        );

        // Platform role (guard: web)
        $platformRoleObj = Role::findByName($platformRole->value, 'web');
        if (!$user->hasRole($platformRoleObj)) {
            $user->assignRole($platformRoleObj);
        }

        // Store role (guard: merchant)
        $storeRoleObj = Role::findByName($storeRole->value, 'merchant');
        if (!$user->hasRole($storeRoleObj)) {
            $user->assignRole($storeRoleObj);
        }

        return $user;
    }

    private function ensureMembership(
        Store $store,
        User $user,
        string $invitedBy,
        StoreRoleEnum $role,
    ): void {
        StoreMembership::firstOrCreate(
            ['store_id' => $store->id, 'user_id' => $user->id],
            [
                'invited_by' => $invitedBy,
                'is_active'  => true,
            ]
        );

        $roleObj = Role::findByName($role->value, 'merchant');
        if (!$user->hasRole($roleObj)) {
            $user->assignRole($roleObj);
        }
    }
}
