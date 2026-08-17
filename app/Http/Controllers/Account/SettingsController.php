<?php
namespace App\Http\Controllers\Account;

use App\Domains\Account\DTOs\SettingsData;
use App\Domains\Account\Services\AccountService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\Settings\UpdateSettingsRequest;

class SettingsController extends Controller
{
    public function update(UpdateSettingsRequest $request, AccountService $service)
    {
        $service->updateSettings(
            auth()->user(),
            SettingsData::fromArray($request->validated())
        );

        return back()->with('success', __('notifications.settings_updated'));
    }
}
