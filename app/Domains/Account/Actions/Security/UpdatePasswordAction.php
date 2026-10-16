<?php

namespace App\Domains\Account\Actions\Security;

use App\Domains\Account\DTOs\PasswordData;
use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use App\Services\Auth\AuthSecurityLogger;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UpdatePasswordAction
{
    public function execute(User $user, PasswordData $data): void
    {
        if (! Hash::check($data->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => __('validation.current_password'),
            ]);
        }

        $user->update([
            'password' => Hash::make($data->new_password),
        ]);

        app(AuthSecurityLogger::class)->passwordChanged($user);
        $user->notify(new PasswordChangedNotification($user->email));
    }
}
