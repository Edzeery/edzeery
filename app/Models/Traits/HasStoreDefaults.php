<?php

namespace App\Models\Traits;
trait HasStoreDefaults
{
    public function initializeHasStoreDefaults(): void
    {
        if (! $this->exists) {
            $this->saving(function ($model) {
                if ($model->relationLoaded('settings') && ! $model->relationLoaded('settings')) {
                    $model->settings()->create();
                }
            });
        }
    }
}
