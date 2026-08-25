<?php

namespace App\Console\Commands;

use App\Domains\Order\Services\OrderService;
use App\Models\Orders\Order;
use App\Models\Stores\Team\StoreMembership;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoCancelPendingOrders extends Command
{
    protected $signature = 'orders:auto-cancel-pending {--hours=48 : Orders older than this many hours}';

    protected $description = 'Cancel pending orders older than N hours to release reserved stock';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $cutoff = Carbon::now()->subHours($hours);

        $orders = Order::where('status_id', function ($q) use ($cutoff) {
            $q->select('id')
                ->from('statuses')
                ->where('key', 'pending')
                ->where('type', 'order');
        })
            ->where('created_at', '<', $cutoff)
            ->with('store')
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No pending orders to cancel.');
            return static::SUCCESS;
        }

        $service = app(OrderService::class);
        $cancelled = 0;
        $errors = 0;

        foreach ($orders as $order) {
            try {
                $service->transition($order, 'cancelled', 'Auto-cancelled: pending for more than ' . $hours . 'h');
                $cancelled++;
            } catch (\Exception $e) {
                $errors++;
                $this->error("Failed to cancel order {$order->number}: {$e->getMessage()}");
            }
        }

        $this->info("Cancelled: {$cancelled} | Errors: {$errors} | Total checked: {$orders->count()}");
        return $errors > 0 ? static::FAILURE : static::SUCCESS;
    }
}
