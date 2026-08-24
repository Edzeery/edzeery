<?php

namespace App\Services\Stores;

use App\Models\Stores\Store;
use Illuminate\Support\Facades\DB;

class StorefrontThemeService
{
    /**
     * Persist the landing template and theme settings atomically.
     *
     * @param  array{primary_color:string,secondary_color:string,font_family:string,homepage_sections:list<string>,section_content:array}  $themeData
     */
    public static function save(Store $store, string $template, array $themeData): void
    {
        DB::transaction(function () use ($store, $template, $themeData): void {
            $store->update(['landing_template' => $template]);

            $store->theme()->updateOrCreate([], $themeData);
        });
    }
}
