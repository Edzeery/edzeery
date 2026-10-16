<?php

namespace App\Domains\Account\Actions\Profile;

use App\Domains\Account\DTOs\ProfileData;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UpdateProfileAction
{
    public function execute(User $user, ProfileData $data): void
    {
        $user->update([
            'name' => $data->name ?? $user->name,
            'country_id' => filled($data->country_id) ? $data->country_id : null,
            'state_id' => filled($data->state_id) ? $data->state_id : null,
            'city_id' => filled($data->city_id) ? $data->city_id : null,
        ]);

        if ($data->email && $data->email !== $user->email) {
            $user->forceFill([
                'email' => $data->email,
                'email_verified_at' => null,
            ])->save();
        }

        $profileData = [
            'full_name' => $data->name ?? $data->full_name,
            'phone' => $data->phone,
            'address' => $data->address,
            'birthdate' => filled($data->birthdate) ? $data->birthdate : null,
        ];

        $existing = $user->profile;

        if ($data->profile_picture instanceof UploadedFile) {
            if ($existing?->profile_picture) {
                Storage::disk('public')->delete($existing->profile_picture);
            }
            $profileData['profile_picture'] = $data->profile_picture->store('profiles', 'public');
        } elseif ($data->remove_avatar && $existing?->profile_picture) {
            Storage::disk('public')->delete($existing->profile_picture);
            $profileData['profile_picture'] = null;
        }

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );
    }
}
