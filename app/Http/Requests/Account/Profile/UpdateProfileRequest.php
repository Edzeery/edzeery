<?php

namespace App\Http\Requests\Account\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = auth()->user();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'birthdate' => ['nullable', 'date'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'state_id' => ['nullable', 'exists:states,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
            'current_password' => [
                Rule::requiredIf(fn () => $user && $this->input('email') !== $user->email),
                'nullable',
                'current_password',
            ],
        ];
    }

    /**
     * Rules evaluated against a plain data payload, for Livewire/Volt actions
     * that validate component state instead of a live HTTP request.
     */
    public static function rulesFor(array $data = []): array
    {
        return (new static)->replace($data)->rules();
    }
}
