<?php

namespace App\Console\Commands;

use App\Models\Stores\Store;
use App\Support\StoreSlugRules;
use Illuminate\Console\Command;

class CheckReservedStoreSlugs extends Command
{
    protected $signature = 'store:check-reserved-slugs';

    protected $description = 'List every store whose slug is reserved by the platform (read-only)';

    public function handle(): int
    {
        $stores = Store::query()
            ->whereIn('slug', StoreSlugRules::reservedSlugs())
            ->orderBy('slug')
            ->get(['id', 'slug', 'name']);

        if ($stores->isEmpty()) {
            $this->info('No stores use a reserved slug. Ready to deploy the guard.');

            return static::SUCCESS;
        }

        $this->warn('Found store(s) using a reserved platform slug:');
        $this->table(
            ['ID', 'Slug', 'Name'],
            $stores->map(fn (Store $store) => [$store->id, $store->slug, $store->name])->all(),
        );

        return static::SUCCESS;
    }
}