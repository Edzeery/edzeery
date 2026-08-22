<?php

namespace App\Models\Traits;

trait HasStoreDefaults
{
    public function initializeHasStoreDefaults(): void
    {
        if (! $this->exists) {
            $this->created(function ($model) {
                $model->settings()->firstOrCreate(['store_id' => $model->id]);
            });
        }
    }
}
