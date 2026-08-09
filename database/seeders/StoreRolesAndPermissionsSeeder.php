<?php

namespace Database\Seeders;

use App\Enums\Platform\UserRoleEnum;
use Illuminate\Database\Seeder;
use App\Enums\Store\StoreRoleEnum;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Support\StoreRoles;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class StoreRolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        try {
            // =========================
            // Step 1: Create Store Permissions
            // =========================
            foreach (StorePermissionEnum::cases() as $permissionEnum) {
                Permission::firstOrCreate([
                    'name' => $permissionEnum->value,
                    'guard_name' => 'merchant',
                ]);
            }

            // =========================
            // Step 4: Create Store Roles and Assign Permissions
            // =========================
            foreach (StoreRoleEnum::cases() as $roleEnum) {

                $role = Role::firstOrCreate([
                    'name' => $roleEnum->value,
                    'guard_name' => 'merchant',
                ]);

                $permissions = StoreRoles::permissions($roleEnum); // array of names
                $permissionObjects = Permission::whereIn('name', $permissions)
                    ->get();
                if ($permissionObjects->isNotEmpty()) {
                    $role->syncPermissions($permissionObjects); // فقط المصفوفة، store_id مرتبط بالدور

                }
            }
            // =========================
            // Step 2: Create Merchant User
            // =========================
            $merchantUser = $this->createUser(
                'merchant@edzeery.com',
                'Merchant',
                UserRoleEnum::MERCHANT,
                StoreRoleEnum::OWNER,
                'merchant'
            );

            // =========================
            // Step 3: Create a default Store
            // =========================
            $store = Store::firstOrCreate(
                ['slug' => 'default-merchant-store'],
                [
                    'user_id' => $merchantUser->id,
                    'name' => 'Default Merchant Store',
                    'status' => 'active',
                ]
            );
 
            // =========================
            // Step 5: Assign Owner Role to Merchant in this Store
            // =========================

            $Membership = StoreMembership::create([
                'store_id' => $store->id,
                'user_id'  => $store->user_id,
                'invited_by'  => $store->user_id,
                'is_active' => true,
            ]);

            if ($Membership && !$merchantUser->hasRole(StoreRoleEnum::OWNER,'merchant')) {
                $merchantUser->guard_name = 'merchant';
                $merchantUser->assignRole(StoreRoleEnum::OWNER);
            }
            $this->command?->info(' Store Roles And Permissions Seeder seeded successfully.');
        } catch (\Exception $e) {
            $this->command?->error($e->getMessage());
        }
    }

    private function createUser(
        string $email,
        string $name,
        UserRoleEnum $role,
        StoreRoleEnum $store_role,
        $guard_name = null
    ): ?User {

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
            ]
        );
        if (!$user->hasRole($role)) {
            $user->assignRole($role->value);
        }

        if (!$user->merchant()->hasRole($store_role) && $guard_name) {
            $user->merchant()->assignRole($store_role->value);
        }

        return  $user;
    }
}
