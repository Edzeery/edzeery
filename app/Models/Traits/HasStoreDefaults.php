<?php

namespace App\Models\Traits;

trait HasStoreDefaults
{
    public function initializeHasStoreDefaults(): void
    {
        if (! $this->exists) {
            $this->created(function ($model) {
                $model->settings()->updateOrCreate(
                    ['store_id' => $model->id],
                );
            });
        }
    }
}
