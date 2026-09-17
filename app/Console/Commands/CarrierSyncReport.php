<?php

namespace App\Console\Commands;

use App\Domains\Shipping\Models\CarrierSyncRun;
use Illuminate\Console\Command;

class CarrierSyncReport extends Command
{
    protected $signature = 'carrier-sync:report {--hours=24 : Look back this many hours}';

    protected $description = 'Summarize NOEST tracking sync pilot observability per provider';

    public function handle(): int
    {
        $hours = max(1, (int) $this->option('hours'));
        $cutoff = now()->subHours($hours);

        // Single query loads the window with provider + store names; counts
        // and durations are aggregated in-memory (portable across sqlite/mysql).
        $runs = CarrierSyncRun::query()
            ->with('store:id,name')
            ->with('shippingProvider:id,name')
            ->where('started_at', '>=', $cutoff)
            ->get();

        if ($runs->isEmpty()) {
            $this->info("No carrier sync runs in the last {$hours} hour(s).");
            return static::SUCCESS;
        }

        $summary = $runs->groupBy('shipping_provider_id')->map(function ($group) {
            return [
                'store' => $group->first()->store?->name,
                'provider' => $group->first()->shippingProvider?->name,
                'runs' => $group->count(),
                'attempted' => (int) $group->sum('attempted'),
                'updated' => (int) $group->sum('updated'),
                'unknown' => (int) $group->sum('unknown'),
                'failed' => (int) $group->sum('failed'),
                'avg_duration_s' => round($group->avg(
                    fn ($run) => max(0, $run->finished_at->diffInSeconds($run->started_at))
                ) ?: 0, 1),
            ];
        })->sortByDesc('attempted')->values();

        $this->table(
            ['Store', 'Provider', 'Runs', 'Attempted', 'Updated', 'Unknown', 'Failed', 'Success rate', 'Avg duration (s)'],
            $summary->map(fn ($row) => [
                $row['store'],
                $row['provider'],
                $row['runs'],
                $row['attempted'],
                $row['updated'],
                $row['unknown'],
                $row['failed'],
                $this->percent($row['updated'], $row['attempted']),
                $row['avg_duration_s'],
            ])->all(),
        );

        return static::SUCCESS;
    }

    private function percent(int $part, int $total): string
    {
        if ($total <= 0) {
            return '—';
        }

        return round(($part / $total) * 100, 1).'%';
    }
}