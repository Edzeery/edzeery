<?php

namespace App\Http\Requests\Account\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'language' => 'nullable|in:ar,en,fr,es',
            'theme' => 'nullable|in:light,dark,system',
            'timezone' => 'nullable|string|max:50',
            'date_format' => 'nullable|in:Y-m-d,d/m/Y,m/d/Y',
            'email_notifications' => 'nullable|boolean',
            'order_notifications' => 'nullable|boolean',
            'stock_notifications' => 'nullable|boolean',
            'marketing_notifications' => 'nullable|boolean',
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
