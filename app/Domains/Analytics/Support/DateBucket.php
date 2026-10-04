<?php

namespace App\Domains\Analytics\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final class DateBucket
{
    /**
     * Axis resolution from the effective window length: a single day or less is
     * plotted hourly, up to two months daily, anything longer monthly.
     */
    public static function granularityFor(CarbonImmutable $from, CarbonImmutable $to): string
    {
        // Order-insensitive: the axis walk in generateSeries() normalises a
        // reversed window too, and both call sites must agree on the resolution.
        $start = $from->min($to);
        $end = $from->max($to);

        // Carbon 3 returns a truncated float here, so a one-day window measures
        // 0; adding one gives the inclusive number of days the axis spans.
        $days = (int) $start->diffInDays($end) + 1;

        if ($days <= 1) {
            return 'hour';
        }

        return $days <= 62 ? 'day' : 'month';
    }

    /**
     * Groups orders by the period they fall into **in the store timezone**.
     * created_at is stored in UTC, so the key is shifted by the offset that was
     * in effect at the end of the window before being formatted.
     *
     * @param  int  $offsetSeconds  Always cast to int here; it never comes from
     *                              user input (it is the store timezone offset).
     */
    public function bucketExpression(string $driver, string $granularity, int $offsetSeconds): string
    {
        $offsetSeconds = (int) $offsetSeconds;
        $format = $this->sqlFormat($driver, $granularity);

        return match ($driver) {
            'sqlite' => "strftime('{$format}', orders.created_at, '{$offsetSeconds} seconds') as bucket",
            'mysql', 'mariadb' => "DATE_FORMAT(DATE_ADD(orders.created_at, INTERVAL {$offsetSeconds} SECOND), '{$format}') as bucket",
            'pgsql' => "to_char(orders.created_at + interval '{$offsetSeconds} seconds', '{$format}') as bucket",
            default => "DATE_FORMAT(orders.created_at, '{$format}') as bucket",
        };
    }

    /**
     * strftime, DATE_FORMAT and to_char each spell the same instant
     * differently, so the format has to follow the driver.
     *
     * MySQL and MariaDB need percent-prefixed specifiers. Handing DATE_FORMAT a
     * PHP-style 'Y-m-d H' makes it return that text verbatim, so every row is
     * grouped under one literal bucket key that the PHP side never matches and
     * the trend chart renders flat. The default branch shares the MySQL
     * formats, since MySQL is the production driver.
     */
    private function sqlFormat(string $driver, string $granularity): string
    {
        return match ($driver) {
            'sqlite' => match ($granularity) {
                'hour' => '%Y-%m-%d %H',
                'month' => '%Y-%m',
                default => '%Y-%m-%d',
            },
            'pgsql' => match ($granularity) {
                'hour' => 'YYYY-MM-DD HH24',
                'month' => 'YYYY-MM',
                default => 'YYYY-MM-DD',
            },
            default => match ($granularity) {
                'hour' => '%Y-%m-%d %H',
                'month' => '%Y-%m',
                default => '%Y-%m-%d',
            },
        };
    }

    /**
     * The key the PHP side of generateSeries() rebuilds, so a bucket written by
     * SQL and a bucket walked over by Carbon are always the same string.
     */
    private function keyFormat(string $granularity): string
    {
        return match ($granularity) {
            'hour' => 'Y-m-d H',
            'month' => 'Y-m',
            default => 'Y-m-d',
        };
    }

    /**
     * Zero-filled axis for the window, labelled for display and keyed exactly
     * like the SQL bucket expression.
     *
     * @param  callable(): iterable  $fetcher  Rows carrying bucket/revenue/orders.
     * @param  string  $driver  Kept so callers stay symmetric with
     *                          bucketExpression(); the bucket keys themselves are
     *                          rebuilt with the shared PHP format.
     * @return array{labels: array<int, string>, revenue: array<int, float>, orders: array<int, int>, trend: Collection<string, array{revenue: float, orders: int}>}
     */
    public function generateSeries(
        CarbonImmutable $from,
        ?CarbonImmutable $to,
        string $driver,
        string $timezone,
        int $offsetSeconds,
        callable $fetcher
    ): array {
        $end = $to ?? $from->endOfDay();

        if ($end->lt($from)) {
            [$from, $end] = [$end->startOfDay(), $from->endOfDay()];
        }

        $map = Collection::make($fetcher())
            ->keyBy('bucket')
            ->map(fn ($row) => [
                'revenue' => (float) ($row->revenue ?? 0),
                'orders' => (int) ($row->orders ?? 0),
            ]);

        $granularity = self::granularityFor($from, $end);
        $keyFormat = $this->keyFormat($granularity);

        $start = $from->setTimezone($timezone);
        $finish = $end->setTimezone($timezone);

        [$cursor, $limit, $advance] = match ($granularity) {
            'hour' => [$start->startOfHour(), $finish->endOfHour(), 'addHour'],
            'month' => [$start->startOfMonth(), $finish->startOfMonth(), 'addMonth'],
            default => [$start->startOfDay(), $finish->startOfDay(), 'addDay'],
        };

        $labels = $revenue = $orders = [];

        while ($cursor->lte($limit)) {
            // The SQL side shifts the UTC instant; do the same here so the axis
            // key matches the bucket the rows were grouped into.
            $key = $cursor->utc()->addSeconds($offsetSeconds)->format($keyFormat);

            $labels[] = $this->label($granularity, $cursor);
            $revenue[] = (float) ($map->get($key)['revenue'] ?? 0);
            $orders[] = (int) ($map->get($key)['orders'] ?? 0);

            $cursor = $cursor->{$advance}();
        }

        return [
            'labels' => $labels,
            'revenue' => $revenue,
            'orders' => $orders,
            'trend' => $map,
        ];
    }

    private function label(string $granularity, CarbonImmutable $cursor): string
    {
        return match ($granularity) {
            'hour' => $cursor->format('H:00'),
            'month' => $cursor->format('m/Y'),
            default => $cursor->format('d/m'),
        };
    }
}
