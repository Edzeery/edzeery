<?php

namespace App\Domains\Account\Actions\Security;

use App\Domains\Account\DTOs\PasswordData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UpdatePasswordAction
{
    public function execute(User $user, PasswordData $data): void
    {
        if (!Hash::check($data->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Current password is incorrect',
            ]);
        }

        $user->update([
            'password' => Hash::make($data->new_password),
        ]);
    }
}
