<?php
namespace App\Http\Controllers\Account;

use App\Domains\Account\DTOs\PasswordData;
use App\Domains\Account\Services\AccountService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\Password\UpdatePasswordRequest;

class SecurityController extends Controller
{
    public function update(UpdatePasswordRequest $request, AccountService $service)
    {
        $service->updatePassword(
            auth()->user(),
            PasswordData::fromArray($request->validated())
        );

        return back()->with('success', __('notifications.password_updated'));
    }
}
