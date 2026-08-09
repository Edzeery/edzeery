<?php

namespace App\Domains\Account\Actions\Profile;

use App\Domains\Account\DTOs\ProfileData;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UpdateProfileAction
{
    public function execute(User $user, ProfileData $data): void
    {
        // تحديث location
        $user->update([
            'country_id' => $data->country_id,
            'state_id' => $data->state_id,
            'city_id' => $data->city_id,
        ]);

        $profileData = [
            'full_name' => $data->full_name,
            'phone' => $data->phone,
            'address' => $data->address,
            'birthdate' => $data->birthdate,
        ];

        // رفع الصورة
        if ($data->profile_picture instanceof \Illuminate\Http\UploadedFile) {
            $path = $data->profile_picture->store('profiles', 'public');
            $profileData['profile_picture'] = $path;
        }

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );
    }
}
