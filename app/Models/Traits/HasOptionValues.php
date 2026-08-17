<?php

namespace App\Models\Traits;

use App\Models\Products\ProductOptionValue;

trait HasOptionValues
{
    public function addValue(string $value): ProductOptionValue
    {
        return $this->values()->firstOrCreate([
            'value' => $value,
        ], [
            'store_id' => $this->store_id ?? currentStoreId(),
        ]);
    }

    public function hasValue(string $value): bool
    {
        return $this->values()
            ->where('value', $value)
            ->exists();
    }
}
