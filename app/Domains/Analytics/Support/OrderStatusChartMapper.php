<?php

namespace App\Domains\Analytics\Support;

use App\Domains\Status\StatusResolver;
use Illuminate\Support\Str;

final class OrderStatusChartMapper
{
    /**
     * @param  \Illuminate\Support\Collection<int, object>  $rows
     * @return \Illuminate\Support\Collection<int, object>
     */
    public function map($rows, ?string $storeId = null)
    {
        $resolvedDomain = StatusResolver::domain('order', $storeId);

        return $rows->map(function ($row) use ($resolvedDomain) {
            $key = $row->key;
            $label = null;

            if ($key) {
                $tk = 'statuses.order.'.$key;
                $tr = trans($tk);
                if ($tr !== $tk && ! empty($tr)) {
                    $label = $tr;
                } else {
                    $alt = str_replace('cancelled', 'canceled', $key);
                    if ($alt !== $key) {
                        $tak = 'statuses.order.'.$alt;
                        $tar = trans($tak);
                        if ($tar !== $tak && ! empty($tar)) {
                            $label = $tar;
                        }
                    }
                }
            }

            if (empty($label) && $key && isset($resolvedDomain[$key])) {
                $resolved = $resolvedDomain[$key];
                if (! empty($resolved->label)) {
                    $label = $resolved->label;
                }
            }

            if (empty($label) && $key) {
                $label = (string) Str::of($key)->replace('_', ' ')->title();
            }

            $hex = '#9ca3af';
            if ($key && isset($resolvedDomain[$key])) {
                $resolved = $resolvedDomain[$key];
                if (! empty($resolved->hex)) {
                    $hex = $resolved->hex;
                }
            }

            return (object) [
                'key' => $key,
                'count' => (int) $row->count,
                'label' => $label,
                'hex' => $hex,
            ];
        });
    }
}
