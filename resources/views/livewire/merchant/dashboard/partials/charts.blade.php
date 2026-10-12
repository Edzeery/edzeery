@php
    // D3: the one colour-per-group map, read from config so the legend dots
    // (server-rendered) and the chart series (client-rendered) can never drift.
    $groupColors = config('analytics.group_colors');

    // A server-side dot for a legend row: a group token becomes a class, the
    // resolved chart tokens become a class, and a store's own status hex falls
    // back to an inline colour (the delivery view still reads those).
    $dotFor = function (string $key, ?string $hex) use ($groupColors): array {
        if (isset($groupColors[$key])) {
            return ['class' => 'bg-'.$groupColors[$key], 'style' => null];
        }

        $tokenClass = match ($hex) {
            'token:accent' => 'bg-accent-500',
            'token:success' => 'bg-success-500',
            'token:warning' => 'bg-warning-500',
            'token:danger' => 'bg-danger-500',
            'token:info' => 'bg-info-500',
            'token:muted' => 'bg-ink-muted',
            default => null,
        };

        return ['class' => $tokenClass ?? 'rounded-full', 'style' => $tokenClass ? null : 'background-color: '.($hex ?: 'currentColor')];
    };
@endphp

<div wire:key="dash-charts-{{ $filter->hash() }}" x-data="{
    chartLabels: {{ json_encode($trend['labels'] ?? []) }},
    chartSeries: {{ json_encode($trend['series'] ?? []) }},
    trendView: '{{ $trend['view'] ?? 'confirmation' }}',
    groupColors: {{ json_encode($groupColors) }},
    statusLabels: {{ json_encode($statusBreakdown->pluck('label')->values()) }},
    statusKeys: {{ json_encode($statusBreakdown->pluck('key')->values()) }},
    statusCounts: {{ json_encode($statusBreakdown->pluck('count')->values()->map(fn($v) => (int) $v)) }},
    statusPercents: {{ json_encode($statusBreakdown->pluck('percent')->values()->map(fn($v) => (int) $v)) }},
    statusHex: {{ json_encode($statusBreakdown->pluck('hex')->values()) }},
    renderCharts() {
        let tries = 0;
        const attempt = () => {
            if (window.Chart && typeof window.renderDashboardCharts === 'function') {
                window.renderDashboardCharts(this);
            } else if (tries < 20) {
                tries++;
                setTimeout(attempt, 50);
            }
        };
        this.$nextTick(attempt);
    }
}" x-init="renderCharts()">

    <div class="edz-stagger grid grid-cols-1 gap-6 @if (empty($narrowCharts)) lg:grid-cols-3 @endif mb-6">
        {{-- Trend — one chart per stats view. Confirmation stacks the seven
             groups as bars (lines past 62 buckets); Delivery keeps its bars
             plus the delivered-revenue line. --}}
        <div class="lg:col-span-2 edz-card edz-card--padded">
            <h3 class="text-sm font-semibold tracking-tight text-ink mb-4">
                {{ ($trend['view'] ?? 'confirmation') === 'delivery'
                    ? __('dashboard.chart_delivery_trend', ['period' => $periodLabel])
                    : __('dashboard.chart_confirmation_trend', ['period' => $periodLabel]) }}
            </h3>
            @if (! empty($trend['labels']))
                <div class="h-64"><canvas id="salesChart" wire:ignore></canvas></div>
                {{-- HTML legend (D3): RTL-safe, so a label and its digits never
                     swap places. The canvas legend is switched off below. --}}
                <ul class="mt-4 flex flex-wrap gap-x-4 gap-y-2">
                    @foreach ($trend['series'] as $s)
                        @php($dot = $dotFor($s['key'], $s['hex']))
                        <li class="flex items-center gap-2 text-xs text-ink-muted">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $dot['class'] }}" @if ($dot['style']) style="{{ $dot['style'] }}" @endif></span>
                            <span>{{ $s['label'] }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="h-64 flex items-center justify-center"><p class="text-sm text-ink-muted">{{ __('dashboard.no_data') }}</p></div>
            @endif
        </div>

        {{-- Status doughnut — the confirmation groups in Confirmation, the
             shipped statuses in Delivery. --}}
        <div class="edz-card edz-card--padded">
            <h3 class="text-sm font-semibold tracking-tight text-ink mb-4">
                {{ ($trend['view'] ?? 'confirmation') === 'delivery'
                    ? __('dashboard.chart_status_delivery')
                    : __('dashboard.chart_status_confirmation') }}
            </h3>
            @if ($statusBreakdown->isNotEmpty())
                <div class="h-64"><canvas id="statusChart" wire:ignore></canvas></div>
                {{-- HTML legend (D3): "label — <bdi>count</bdi> (<bdi dir=ltr>pct</bdi>)". --}}
                <ul class="mt-4 flex flex-wrap gap-x-4 gap-y-2">
                    @foreach ($statusBreakdown as $slice)
                        @php($dot = $dotFor($slice->key, $slice->hex))
                        <li class="flex items-center gap-2 text-xs text-ink-muted">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $dot['class'] }}" @if ($dot['style']) style="{{ $dot['style'] }}" @endif></span>
                            <span>
                                {{ $slice->label }} — <bdi>{{ $slice->count }}</bdi> (<bdi dir="ltr">{{ $slice->percent }}%</bdi>)
                            </span>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="h-64 flex items-center justify-center"><p class="text-sm text-ink-muted">{{ __('dashboard.no_data') }}</p></div>
            @endif
        </div>
    </div>

    <script>
        window.__dashCharts = window.__dashCharts || {};
        window.renderDashboardCharts = function(data) {
            if (data === undefined) return;
            window.__dashCharts._lastData = data;
            const root = getComputedStyle(document.documentElement);
            const cssVar = (name) => root.getPropertyValue(name)?.trim() || null;

            // Tokens ship as "r, g, b" triplets; Chart.js wants a colour.
            const toRgbFromTriplet = (t) => {
                const p = String(t ?? '').replace(/[^0-9,\s]/g, '').split(',').map(s => parseInt(s.trim(), 10));
                return p.length >= 3 && !p.some(isNaN) ? `rgb(${p[0]}, ${p[1]}, ${p[2]})` : null;
            };
            const parseColor = (input) => input ? String(input).trim() : null;
            const toRgba = (input, alpha = 0.08) => {
                const c = parseColor(input);
                if (!c) return `rgba(107, 114, 128, ${alpha})`;
                if (c.startsWith('rgba(')) return c.replace(/,[^,]+\)$/, `, ${alpha})`);
                if (c.startsWith('rgb(')) return c.replace('rgb(', 'rgba(').replace(')', `, ${alpha})`);
                const hex = c[0] === '#' ? (c.length === 4 ? c.slice(1).split('').map(x => x + x).join('') : c.slice(1)) : '';
                const rgb = hex.length === 6 ? [0, 2, 4].map(i => parseInt(hex.substring(i, i + 2), 16)) : [];
                return rgb.length && !rgb.some(isNaN) ? `rgba(${rgb.join(', ')}, ${alpha})` : `rgba(107, 114, 128, ${alpha})`;
            };

            const themeColor = (name, fallback) => toRgbFromTriplet(cssVar(name)) || cssVar(name) || fallback;
            const resolvedFontColor = themeColor('--edz-color-text-soft', '#6b7280');
            const resolvedGridColor = themeColor('--edz-color-border', '#e5e7eb');
            const resolvedAccent = themeColor('--edz-color-accent-500', '#6366f1');
            // --edz-color-ink is not a token, so a near-black fallback made the
            // centre total vanish on the dark card; --edz-color-text is the
            // theme body colour and follows it in both directions.
            const resolvedText = themeColor('--edz-color-text', '#101828');
            // .edz-card paints rgb(var(--edz-color-surface)); slices are split
            // with that same colour, so one shared hex still reads as two.
            const resolvedSurface = themeColor('--edz-color-surface', 'rgb(255, 255, 255)');
            const num = (v) => new Intl.NumberFormat(undefined, { maximumFractionDigits: 0 }).format(v);
            const currency = @js(__('stores.currency_symbol'));

            Chart.defaults.color = resolvedFontColor;
            Chart.defaults.font.family = "'Inter', 'IBM Plex Sans Arabic', sans-serif";
            const tipDefaults = Chart.defaults.plugins.tooltip;
            if (tipDefaults) { tipDefaults.titleColor = resolvedFontColor; tipDefaults.bodyColor = resolvedFontColor; }

            // Neither axis has a meaningful negative half, and an all-zero
            // series would draw the baseline at the top gridline, so an empty
            // window still gets a four-count scale instead of a full-height bar.
            const hasPositive = (values) => Array.isArray(values) && values.some(v => Number(v) > 0);

            // D3: the confirmation trend is stacked bars up to 62 buckets (a
            // day-per-point month); past that a bar per group per bucket would
            // be unreadable, so it falls back to lines.
            const isConfirmation = data.trendView !== 'delivery';
            const stackedBars = isConfirmation && Array.isArray(data.chartLabels)
                && data.chartLabels.length > 0 && data.chartLabels.length <= 62;

            // Only an hourly axis holds 24 mostly-empty buckets, which is why
            // its zero buckets carry no marker; wider axes keep theirs.
            const isHourly = (labels) => Array.isArray(labels) && labels.some(l => String(l).includes(':'));
            const showPoints = (values) => Array.isArray(values) && values.length > 0 && values.length <= 31;
            const pointRadius = (ctx, values) => ! showPoints(values) ? 0
                : (isHourly(data.chartLabels) && !(Number(ctx.raw) > 0) ? 0 : 3);
            const pointHoverRadius = (values) => showPoints(values) ? 5 : 0;

            // One dataset per server metric: `axis` picks the side, `type` picks
            // line/bar, and `token:accent` is the theme's own accent colour.
            const series = Array.isArray(data.chartSeries) ? data.chartSeries : [];
            // Named theme tokens the server can hand a chart without knowing the
            // active theme.
            const tokenColors = {
                'token:accent': resolvedAccent,
                'token:success': themeColor('--edz-color-success-500', '#12b76a'),
                'token:warning': themeColor('--edz-color-warning-500', '#f79009'),
                'token:danger': themeColor('--edz-color-danger-500', '#f04438'),
                'token:info': themeColor('--edz-color-info-500', '#0284c7'),
                'token:muted': themeColor('--edz-color-text-soft', '#667085'),
            };
            const colorFor = (hex) => tokenColors[hex] ?? (parseColor(hex) || resolvedText);
            // D3: a group key resolves through the single config map, so the
            // doughnut, the trend and the (server-rendered) legend agree.
            const groupColors = data.groupColors || {};
            const groupColor = (key, fallback) => groupColors[key]
                ? themeColor('--edz-color-' + groupColors[key], fallback)
                : colorFor(fallback);
            const valuesFor = (axis) => series.filter(s => s.axis === axis).flatMap(s => Array.isArray(s.values) ? s.values : []);
            const yValues = valuesFor('y');
            const y1Values = valuesFor('y1');

            const datasets = series.map(s => {
                const color = isConfirmation ? groupColor(s.key, s.hex) : colorFor(s.hex);
                const type = stackedBars ? 'bar' : (isConfirmation ? 'line' : s.type);
                const isLine = type === 'line';
                const isStackedBar = stackedBars;
                return {
                    label: s.label,
                    type: type,
                    data: s.values,
                    yAxisID: s.axis,
                    borderColor: isStackedBar ? resolvedSurface : color,
                    backgroundColor: isStackedBar ? color : (isLine ? toRgba(color, 0.08) : toRgba(color, 0.7)),
                    fill: isLine && s.axis === 'y1',
                    tension: 0, stepped: false,
                    // A 2px surface border separates the stacked segments.
                    borderWidth: isStackedBar ? 2 : 2,
                    borderRadius: isLine ? 0 : 3,
                    pointRadius: (ctx) => isLine ? pointRadius(ctx, s.values) : 0,
                    pointHoverRadius: isLine ? pointHoverRadius(s.values) : 0,
                    pointHoverBackgroundColor: color,
                };
            });

            ['s', 'st'].forEach(k => { if (window.__dashCharts[k]) { window.__dashCharts[k].destroy(); window.__dashCharts[k] = null; } });

            const trendCanvas = data.chartLabels && data.chartLabels.length ? document.getElementById('salesChart') : null;
            if (trendCanvas) {
                const scales = {
                    x: { stacked: stackedBars, grid: { color: resolvedGridColor, drawBorder: false }, border: { display: false }, ticks: { color: resolvedFontColor } },
                    y: {
                        position: 'left',
                        beginAtZero: true,
                        stacked: stackedBars,
                        suggestedMax: hasPositive(yValues) ? undefined : 4,
                        grid: { color: resolvedGridColor, drawBorder: false },
                        border: { display: false },
                        ticks: { color: resolvedFontColor, precision: 0 }, // "2.5 orders" is noise
                        title: { display: true, text: @js(__('dashboard.orders')), color: resolvedFontColor }
                    },
                };
                // The currency axis only exists where a metric carries money.
                if (series.some(s => s.axis === 'y1')) {
                    scales.y1 = {
                        position: 'right',
                        beginAtZero: true,
                        stacked: false,
                        suggestedMax: hasPositive(y1Values) ? undefined : 1000,
                        grid: { drawOnChartArea: false },
                        border: { display: false },
                        // Money names its currency in every tick, not only in the header.
                        ticks: { color: resolvedFontColor, callback: (value) => `${num(value)} ${currency}` },
                        title: { display: true, text: @js(__('stores.currency_symbol')), color: resolvedFontColor }
                    };
                }
                const trendTooltip = {
                    callbacks: {
                        title: (items) => items.length ? items[0].label : '',
                        label: (context) => {
                            const value = num(context.parsed.y);
                            return context.dataset.yAxisID === 'y1'
                                ? `${context.dataset.label}: ${value} ${currency}`
                                : `${context.dataset.label}: ${value}`;
                        }
                    }
                };
                // A stacked bucket reads its own total under the per-group lines.
                if (stackedBars) {
                    trendTooltip.callbacks.footer = (items) => {
                        if (! items.length) return '';
                        const total = items.reduce((sum, item) => sum + Number(item.parsed.y || 0), 0);
                        return `${@js(__('dashboard.chart_total'))}: ${num(total)}`;
                    };
                }
                const trendCanvasEl = document.getElementById('salesChart');
                if (trendCanvasEl && Chart.getChart(trendCanvasEl)) Chart.getChart(trendCanvasEl).destroy();
                window.__dashCharts.s = new Chart(trendCanvasEl, {
                    type: stackedBars ? 'bar' : 'line',
                    data: { labels: data.chartLabels, datasets: datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: stackedBars ? 'index' : 'nearest', intersect: ! stackedBars },
                        plugins: {
                            // The legend is server-rendered HTML (D3); the canvas one is off.
                            legend: { display: false },
                            tooltip: trendTooltip
                        },
                        scales: scales
                    }
                });
            }

            const counts = Array.isArray(data.statusCounts) ? data.statusCounts : [];
            const percents = Array.isArray(data.statusPercents) ? data.statusPercents : [];
            const statusCanvas = (data.statusKeys && data.statusKeys.length > 0) ? document.getElementById('statusChart') : null;
            if (statusCanvas) {
                const total = counts.reduce((sum, value) => sum + Number(value || 0), 0);
                const centerTotal = {
                    id: 'edzCenterTotal',
                    afterDraw: (chart) => {
                        const area = chart.chartArea;
                        if (!area) return;
                        const ctx = chart.ctx;
                        const cx = (area.left + area.right) / 2;
                        const cy = (area.top + area.bottom) / 2;
                        ctx.save();
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillStyle = resolvedText; // theme body colour: readable on both cards
                        ctx.font = '800 26px Inter, sans-serif';
                        ctx.fillText(num(total), cx, cy - 10);
                        ctx.fillStyle = resolvedFontColor;
                        ctx.font = '500 11px Inter, sans-serif';
                        ctx.fillText(@js(__('dashboard.chart_total')), cx, cy + 15);
                        ctx.restore();
                    }
                };
                // D3: confirmation slices read the one config map; the delivery
                // doughnut keeps each status's own stored colour.
                const statusColors = data.statusKeys.map((k, i) => groupColors[k]
                    ? themeColor('--edz-color-' + groupColors[k], resolvedText)
                    : colorFor(data.statusHex?.[i]));
                const statusCanvasEl = document.getElementById('statusChart');
                if (statusCanvasEl && Chart.getChart(statusCanvasEl)) Chart.getChart(statusCanvasEl).destroy();
                window.__dashCharts.st = new Chart(statusCanvasEl, {
                    type: 'doughnut',
                    plugins: [centerTotal],
                    data: {
                        labels: data.statusLabels,
                        datasets: [{
                            data: counts,
                            backgroundColor: statusColors,
                            borderColor: data.statusKeys.map(() => resolvedSurface), // card-coloured slice separators
                            borderWidth: 2,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            // The legend is server-rendered HTML (D3).
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    title: (items) => items.length ? items[0].label : '',
                                    label: (context) => {
                                        const i = context.dataIndex;
                                        return `${context.label}: ${num(counts[i] || 0)} (${percents[i] || 0}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        };

        if (!window.__dashCharts.mo) {
            window.__dashCharts.mo = new MutationObserver((ms) => (ms.some(m => m.attributeName === 'class' || m.attributeName === 'data-theme')
                && window.__dashCharts._lastData) && window.renderDashboardCharts(window.__dashCharts._lastData));
            window.__dashCharts.mo.observe(document.documentElement, { attributes: true, attributeFilter: ['class', 'data-theme'] });
        }

        if (window.Livewire) {
            window.addEventListener('livewire:navigating', function() {
                ['s', 'st'].forEach(k => window.__dashCharts[k]?.destroy());
                window.__dashCharts.mo?.disconnect();
                window.__dashCharts = {};
            });
        }
    </script>
</div>
