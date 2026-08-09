<?php

namespace Database\Seeders;

use App\Enums\Platform\PlatformPermissionEnum;
use App\Enums\Platform\UserRoleEnum;
use App\Support\Platform\PlatformRoles;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        try {
            // Reset cache
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            // =========================
            // Permissions (Platform)
            // =========================
            foreach (PlatformPermissionEnum::cases() as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission->value,
                ]);
            }

            // =========================
            // Roles (Platform)
            // =========================
            foreach (UserRoleEnum::cases() as $roleEnum) {
                $role = Role::firstOrCreate([
                    'name' => $roleEnum->value,
                    'guard_name' => 'web',
                ]);

                $permissions = PlatformRoles::permissions($roleEnum);

                if (!empty($permissions)) {
                    $role->syncPermissions($permissions);
                }
            }

            // =========================
            // Test Users (Platform)
            // =========================
            $this->createUser('super@edzeery.com', 'Super Admin', UserRoleEnum::SUPER_ADMIN);
            $this->createUser('admin@edzeery.com', 'Admin', UserRoleEnum::ADMIN);
            $this->createUser('support@edzeery.com', 'Support Agent', UserRoleEnum::SUPPORT_AGENT);
            $this->createUser('tech@edzeery.com', 'Tech Support', UserRoleEnum::TECH_SUPPORT);
            $this->createUser('user@edzeery.com', 'User', UserRoleEnum::USER);

            $this->command?->info('Roles And PermissionsSeeder seeded successfully.');
        } catch (\Exception $e) {
            $this->command?->error($e->getMessage());
        }
    }

    private function createUser(string $email, string $name, UserRoleEnum $role): void
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
            ]
        );

        if (!$user->hasRole($role->value)) {
            $user->assignRole($role->value);
        }
    }
}
