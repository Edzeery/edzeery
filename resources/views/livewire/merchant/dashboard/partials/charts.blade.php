<div wire:key="dash-charts-{{ $filter->hash() }}" x-data="{
    chartLabels: {{ json_encode($trend['labels'] ?? []) }},
    chartSeries: {{ json_encode($trend['series'] ?? []) }},
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

    <div class="edz-stagger grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
        {{-- Trend — one chart per stats view: counts in Confirmation, counts plus delivered revenue in Delivery. --}}
        <div class="lg:col-span-2 edz-card edz-card--padded">
            <h3 class="text-sm font-semibold tracking-tight text-ink mb-4">
                {{ ($trend['view'] ?? 'confirmation') === 'delivery'
                    ? __('dashboard.chart_delivery_trend', ['period' => $periodLabel])
                    : __('dashboard.chart_confirmation_trend', ['period' => $periodLabel]) }}
            </h3>
            @if (! empty($trend['labels']))
                <div class="h-64"><canvas id="salesChart"></canvas></div>
            @else
                <div class="h-64 flex items-center justify-center"><p class="text-sm text-ink-muted">{{ __('dashboard.no_data') }}</p></div>
            @endif
        </div>

        {{-- Status doughnut — the confirmed group reads as one slice in Confirmation, the shipped statuses as their own in Delivery. --}}
        <div class="edz-card edz-card--padded">
            <h3 class="text-sm font-semibold tracking-tight text-ink mb-4">
                {{ ($trend['view'] ?? 'confirmation') === 'delivery'
                    ? __('dashboard.chart_status_delivery')
                    : __('dashboard.chart_status_confirmation') }}
            </h3>
            @if ($statusBreakdown->isNotEmpty())
                <div class="h-64"><canvas id="statusChart"></canvas></div>
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
            if (Chart.defaults.plugins.legend?.labels) Chart.defaults.plugins.legend.labels.color = resolvedFontColor;
            const tipDefaults = Chart.defaults.plugins.tooltip;
            if (tipDefaults) { tipDefaults.titleColor = resolvedFontColor; tipDefaults.bodyColor = resolvedFontColor; }

            // Neither axis has a meaningful negative half, and an all-zero
            // series would draw the baseline at the top gridline, so an empty
            // window still gets a four-count scale instead of a full-height bar.
            const hasPositive = (values) => Array.isArray(values) && values.some(v => Number(v) > 0);

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
            const colorFor = (hex) => hex === 'token:accent' ? resolvedAccent : (parseColor(hex) || resolvedText);
            const valuesFor = (axis) => series.filter(s => s.axis === axis).flatMap(s => Array.isArray(s.values) ? s.values : []);
            const yValues = valuesFor('y');
            const y1Values = valuesFor('y1');

            const datasets = series.map(s => {
                const color = colorFor(s.hex);
                const isLine = s.type === 'line';
                return {
                    label: s.label,
                    // The metric picks its own renderer — delivered and returned
                    // are bars, revenue is a line. Without carrying the type the
                    // chart-level default turned every delivery bar into a line.
                    type: s.type,
                    data: s.values,
                    yAxisID: s.axis,
                    borderColor: color,
                    backgroundColor: isLine ? toRgba(color, 0.08) : toRgba(color, 0.7),
                    fill: isLine && s.axis === 'y1',
                    tension: 0, stepped: false, borderWidth: 2, borderRadius: isLine ? 0 : 3,
                    pointRadius: (ctx) => pointRadius(ctx, s.values),
                    pointHoverRadius: pointHoverRadius(s.values), pointHoverBackgroundColor: color,
                };
            });

            ['s', 'st'].forEach(k => { if (window.__dashCharts[k]) { window.__dashCharts[k].destroy(); window.__dashCharts[k] = null; } });

            const trendCanvas = data.chartLabels && data.chartLabels.length ? document.getElementById('salesChart') : null;
            if (trendCanvas) {
                const scales = {
                    x: { grid: { color: resolvedGridColor, drawBorder: false }, border: { display: false }, ticks: { color: resolvedFontColor } },
                    y: {
                        position: 'left',
                        beginAtZero: true,
                        stacked: false, // bars on one axis sit side by side
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
                        // Money names its currency in every tick, not only in the title.
                        ticks: { color: resolvedFontColor, precision: 0, callback: (value) => `${num(value)} ${currency}` },
                        title: { display: true, text: @js(__('stores.currency_symbol')), color: resolvedFontColor }
                    };
                }
                window.__dashCharts.s = new Chart(trendCanvas, {
                    type: 'line',
                    data: { labels: data.chartLabels, datasets: datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: {
                            legend: { position: 'top', labels: { boxWidth: 12, padding: 16, usePointStyle: true, color: resolvedFontColor } },
                            tooltip: {
                                callbacks: {
                                    title: (items) => items.length ? items[0].label : '',
                                    label: (context) => {
                                        // The money axis carries the store currency; counts stay bare.
                                        const value = num(context.parsed.y);
                                        return context.dataset.yAxisID === 'y1'
                                            ? `${context.dataset.label}: ${value} ${currency}`
                                            : `${context.dataset.label}: ${value}`;
                                    }
                                }
                            }
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
                const statusColors = data.statusKeys.map((k, i) => colorFor(data.statusHex?.[i]));
                window.__dashCharts.st = new Chart(statusCanvas, {
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
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12, padding: 10, usePointStyle: true, color: resolvedFontColor,
                                    // Count and percent straight from the service rows, which sum to 100.
                                    generateLabels: (chart) => data.statusLabels.map((label, i) => ({
                                        text: `${label}: ${num(counts[i] || 0)} (${percents[i] || 0}%)`,
                                        fillStyle: statusColors[i], strokeStyle: resolvedSurface, lineWidth: 0,
                                        hidden: ! chart.getDataVisibility(i), fontColor: resolvedFontColor,
                                        pointStyle: 'circle', datasetIndex: 0, index: i
                                    }))
                                }
                            },
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
