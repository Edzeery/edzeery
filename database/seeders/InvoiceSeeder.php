<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Deterministic line-item counts — a fixed pattern per invoice, so
        // re-seeding produces byte-identical data instead of random sizes.
        $itemCounts = [2, 4, 3, 5, 2];

        Invoice::factory()
            ->count(5)
            ->for($user)
            ->create()
            ->values()
            ->each(function (Invoice $invoice, int $index) use ($itemCounts) {
                $itemCount = $itemCounts[$index % count($itemCounts)];

                InvoiceItem::factory()
                    ->count($itemCount)
                    ->for($invoice)
                    ->create()
                    ->each(function ($item, $itemIndex) {
                        $item->update(['sort_order' => $itemIndex + 1]);
                    });
            });
    }
}
