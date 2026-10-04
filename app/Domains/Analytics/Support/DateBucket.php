<?php

namespace App\Domains\Analytics\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final class DateBucket
{
    public function __construct() {}

    public function bucketExpression(string $driver): string
    {
        return match ($driver) {
            'sqlite' => "strftime('%Y-%m-%d %H', orders.created_at) as bucket",
            'mysql', 'mariadb' => "DATE_FORMAT(orders.created_at, '%Y-%m-%d %H') as bucket",
            'pgsql' => "to_char(orders.created_at, 'YYYY-MM-DD HH24') as bucket",
            default => "DATE_FORMAT(orders.created_at, '%Y-%m-%d %H') as bucket",
        };
    }

    public function generateSeries(CarbonImmutable $from, ?CarbonImmutable $to, string $granularity, callable $fetcher, ?string $locale = null): array
    {
        if (! $from) {
            return [
                'labels' => [],
                'revenue' => [],
                'orders' => [],
                'trend' => collect(),
            ];
        }

        $end = $to ?? $from->endOfDay();
        if ($end->lt($from)) {
            [$from, $end] = [$end->startOfDay(), $from->endOfDay()];
        }

        $rows = Collection::make($fetcher($from, $end));

        $map = $rows->keyBy('bucket')->map(function ($r) {
            return [
                'revenue' => (float) ($r->revenue ?? 0),
                'orders' => (int) ($r->orders ?? 0),
            ];
        });

        $labels = [];
        $revenue = [];
        $ordersArr = [];

        if ($granularity === 'hour') {
            $current = $from->startOfHour();
            $limit = $end->endOfHour();
            while ($current->lte($limit)) {
                $key = $current->format('Y-m-d H');
                $labels[] = $current->format('H:00');
                $val = $map->get($key, ['revenue' => 0, 'orders' => 0]);
                $revenue[] = $val['revenue'];
                $ordersArr[] = $val['orders'];
                $current = $current->addHour();
            }
        } elseif ($granularity === 'day') {
            $current = $from->startOfDay();
            $limit = $end->startOfDay();
            while ($current->lte($limit)) {
                $key = $current->format('Y-m-d');
                $labels[] = $current->format('d/m');
                $val = $map->get($key, ['revenue' => 0, 'orders' => 0]);
                $revenue[] = $val['revenue'];
                $ordersArr[] = $val['orders'];
                $current = $current->addDay();
            }
        } else {
            $current = $from->startOfMonth();
            $limit = $end->startOfMonth();
            while ($current->lte($limit)) {
                $key = $current->format('Y-m');
                $labels[] = $current->format('Y/m');
                $val = $map->get($key, ['revenue' => 0, 'orders' => 0]);
                $revenue[] = $val['revenue'];
                $ordersArr[] = $val['orders'];
                $current = $current->addMonth();
            }
        }

        return [
            'labels' => $labels,
            'revenue' => $revenue,
            'orders' => $ordersArr,
            'trend' => $map,
        ];
    }
}
