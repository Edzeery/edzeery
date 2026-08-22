<?php

namespace App\Filament\SuperAdmin\Resources\Stores\Pages;

use App\Enums\Store\StoreStatusEnum;
use App\Filament\SuperAdmin\Resources\Stores\StoreResource;
use App\Models\Stores\Store;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateStore extends CreateRecord
{
    protected static string $resource = StoreResource::class;
    protected function getFormActions(): array
    {
        return [];
    }
    public function createStore(): void
    {
        $this->validate();

        DB::transaction(function () {

            $store = Store::create([
                'user_id' => auth()->id(),
                'name' => $this->data['name'],
                'slug' => $this->data['slug'],
                'description' => $this->data['description'] ?? null,
                'logo' => uploadPath($this->data['logo'] ?? null),
                'cover' => uploadPath($this->data['cover'] ?? null),
                'status' => StoreStatusEnum::PENDING,
            ]);

            $store->settings()->updateOrCreate(
                ['store_id' => $store->id],
                [
                    'currency' => $this->data['currency'],
                    'currency_symbol' => $this->data['currency_symbol'] ?? 'DA',
                    'language' => $this->data['language'] ?? 'ar',
                    'inventory_tracking' => $this->data['inventory_tracking'] ?? true,
                    'guest_checkout' => $this->data['guest_checkout'] ?? true,
                ]
            );

            $store->seo()->updateOrCreate(
                ['store_id' => $store->id],
                [
                    'meta_title' => $this->data['meta_title'] ?? null,
                    'meta_description' => $this->data['meta_description'] ?? null,
                    'meta_keywords' => $this->data['meta_keywords'] ?? null,
                    'og_image' => uploadPath($this->data['og_image'] ?? null),
                ]
            );

            $store->theme()->updateOrCreate(
                ['store_id' => $store->id],
                [
                    'primary_color' => $this->data['primary_color'] ?? '#000000',
                    'secondary_color' => $this->data['secondary_color'] ?? '#ffffff',
                    'font_family' => $this->data['font_family'] ?? 'Cairo',
                ]
            );
        });

        redirect()->route('filament.super-admin.resources.stores.index');
    }
}
