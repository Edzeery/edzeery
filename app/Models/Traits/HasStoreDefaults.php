<?php

namespace App\Models\Traits;
trait HasStoreDefaults
{
    public function initializeStoreDefaults()
    {
        $this->settings()->create();
        $this->seo()->create();
        $this->theme()->create();
    }
}
