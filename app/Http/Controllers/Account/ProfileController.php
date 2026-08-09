<?php

namespace App\Http\Controllers\Account;

use App\Domains\Account\Actions\Profile\GetProfileAction;
use App\Domains\Account\DTOs\ProfileData;
use App\Domains\Account\Services\AccountService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\Profile\UpdateProfileRequest;

class ProfileController extends Controller
{
    public function index(GetProfileAction $getProfileAction)
    {
        $profile = $getProfileAction->execute(user());
  $countries = \App\Models\Locations\Country::all();
        return view('merchant.account.index', compact('profile' , 'countries'));
    }

    public function update(UpdateProfileRequest $request, AccountService $service)
    {
        $service->updateProfile(
            auth()->user(),
            ProfileData::fromArray($request->validated())
        );

        return back()->with('success', 'Profile updated');
    }
}
