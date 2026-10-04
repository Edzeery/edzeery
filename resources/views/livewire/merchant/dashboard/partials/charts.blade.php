<div wire:key="dash-charts-{{ $filter->hash() }}" x-data="{
    chartDays: {{ json_encode($salesByDay->pluck('date')->values()) }},
    chartRevenue: {{ json_encode($salesByDay->pluck('revenue')->values()->map(fn($v) => (float) $v)) }},
    chartOrders: {{ json_encode($salesByDay->pluck('orders')->values()->map(fn($v) => (int) $v)) }},
    statusLabels: {{ json_encode($ordersByStatus->pluck('label')->values()) }},
    statusKeys: {{ json_encode($ordersByStatus->pluck('key')->values()) }},
    statusCounts: {{ json_encode($ordersByStatus->pluck('count')->values()->map(fn($v) => (int) $v)) }},
    statusHex: {{ json_encode($ordersByStatus->pluck('hex')->values()) }},
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
        {{-- Sales Trend --}}
        <div class="lg:col-span-2 edz-card edz-card--padded">
            <h3 class="text-sm font-semibold tracking-tight text-ink mb-4">
                {{ __('dashboard.sales_trend_for', ['period' => $periodLabel]) }}
            </h3>
            @if ($salesByDay->isNotEmpty())
                <div class="h-64">
                    <canvas id="salesChart"></canvas>
                </div>
            @else
                <div class="h-64 flex items-center justify-center">
                    <p class="text-sm text-ink-muted">{{ __('dashboard.no_data') }}</p>
                </div>
            @endif
        </div>

        {{-- Orders by Status --}}
        <div class="edz-card edz-card--padded">
            <h3 class="text-sm font-semibold tracking-tight text-ink mb-4">{{ __('dashboard.orders_by_status') }}</h3>
            @if ($ordersByStatus->isNotEmpty())
                <div class="h-64">
                    <canvas id="statusChart"></canvas>
                </div>
            @else
                <div class="h-64 flex items-center justify-center">
                    <p class="text-sm text-ink-muted">{{ __('dashboard.no_data') }}</p>
                </div>
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

            const toRgbFromTriplet = (triplet) => {
                if (!triplet) return null;
                const t = triplet.replace(/[^0-9,\s]/g, '').trim();
                const parts = t.split(',').map(p => parseInt(p.trim(), 10));
                return t.includes(',') && parts.length >= 3 && !parts.some(isNaN)
                    ? `rgb(${parts[0]}, ${parts[1]}, ${parts[2]})`
                    : null;
            };

            const parseColor = (input) => input ? String(input).trim() : null;

            const toRgba = (input, alpha = 0.08) => {
                const c = parseColor(input);
                if (!c) return `rgba(107, 114, 128, ${alpha})`;
                if (c.startsWith('rgba(')) return c.replace(/,[^,]+\)$/, `, ${alpha})`);
                if (c.startsWith('rgb(')) return c.replace('rgb(', 'rgba(').replace(')', `, ${alpha})`);
                if (c[0] === '#') {
                    let hex = c.substring(1);
                    if (hex.length === 3) hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
                    const r = parseInt(hex.substring(0, 2), 16);
                    const g = parseInt(hex.substring(2, 4), 16);
                    const b = parseInt(hex.substring(4, 6), 16);
                    if (!Number.isNaN(r) && !Number.isNaN(g) && !Number.isNaN(b)) {
                        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
                    }
                }
                return `rgba(107, 114, 128, ${alpha})`;
            };

            const themeColor = (name, fallback) => toRgbFromTriplet(cssVar(name)) || cssVar(name) || fallback;

            const resolvedFontColor = themeColor('--edz-color-text-soft', '#6b7280');
            const resolvedGridColor = themeColor('--edz-color-border', '#e5e7eb');
            const resolvedAccent = themeColor('--edz-color-accent-500', '#6366f1');
            const resolvedSuccess = themeColor('--edz-color-success-500', '#22c55e');
            const resolvedInk = themeColor('--edz-color-ink', '#111827');

            Chart.defaults.color = resolvedFontColor;
            Chart.defaults.font.family = "'Inter', 'IBM Plex Sans Arabic', sans-serif";
            if (Chart.defaults.plugins.legend?.labels) {
                Chart.defaults.plugins.legend.labels.color = resolvedFontColor;
            }
            if (Chart.defaults.plugins.tooltip) {
                Chart.defaults.plugins.tooltip.titleColor = resolvedFontColor;
                Chart.defaults.plugins.tooltip.bodyColor = resolvedFontColor;
            }

            // Both series are counts and sums, so neither axis has a meaningful
            // negative half. Without an all-zero series Chart.js draws the
            // baseline at its own top gridline and the first point reads as a
            // full-height bar, which looks like real activity on an empty day.
            const hasPositive = (values) => Array.isArray(values) && values.some(v => Number(v) > 0);

            if (window.__dashCharts.s) {
                window.__dashCharts.s.destroy();
                window.__dashCharts.s = null;
            }
            if (window.__dashCharts.st) {
                window.__dashCharts.st.destroy();
                window.__dashCharts.st = null;
            }

            if (data.chartDays && data.chartDays.length > 0) {
                const salesCanvas = document.getElementById('salesChart');
                if (salesCanvas) {
                    window.__dashCharts.s = new Chart(salesCanvas, {
                        type: 'line',
                        data: {
                            labels: data.chartDays,
                            datasets: [{
                                label: @js(__('dashboard.revenue')),
                                data: data.chartRevenue,
                                borderColor: resolvedAccent,
                                backgroundColor: toRgba(resolvedAccent, 0.08),
                                fill: true,
                                tension: 0.4,
                                borderWidth: 2,
                                pointRadius: 0,
                                pointHoverRadius: 5,
                                pointHoverBackgroundColor: resolvedAccent,
                                yAxisID: 'y',
                            }, {
                                label: @js(__('dashboard.total_orders')),
                                data: data.chartOrders,
                                borderColor: resolvedSuccess,
                                backgroundColor: toRgba(resolvedSuccess, 0.05),
                                fill: false,
                                tension: 0.4,
                                borderWidth: 2,
                                pointRadius: 0,
                                pointHoverRadius: 5,
                                pointHoverBackgroundColor: resolvedSuccess,
                                yAxisID: 'y1',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                intersect: false,
                                mode: 'index'
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        boxWidth: 12,
                                        padding: 16,
                                        usePointStyle: true,
                                        color: resolvedFontColor
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: { color: resolvedGridColor, drawBorder: false },
                                    border: { display: false },
                                    ticks: { color: resolvedFontColor }
                                },
                                y: {
                                    position: 'left',
                                    beginAtZero: true,
                                    // An empty period still needs a readable scale.
                                    suggestedMax: hasPositive(data.chartRevenue) ? undefined : 1000,
                                    grid: { color: resolvedGridColor, drawBorder: false },
                                    border: { display: false },
                                    ticks: { color: resolvedFontColor },
                                    title: {
                                        display: true,
                                        text: @js(__('stores.currency_symbol')),
                                        color: resolvedFontColor
                                    }
                                },
                                y1: {
                                    position: 'right',
                                    beginAtZero: true,
                                    suggestedMax: hasPositive(data.chartOrders) ? undefined : 4,
                                    grid: { drawOnChartArea: false },
                                    border: { display: false },
                                    ticks: {
                                        color: resolvedFontColor,
                                        // Order counts are whole numbers; "2.5 orders" is noise.
                                        precision: 0
                                    },
                                    title: {
                                        display: true,
                                        text: @js(__('dashboard.orders')),
                                        color: resolvedFontColor
                                    }
                                },
                            }
                        }
                    });
                }
            }

            if (data.statusKeys && data.statusKeys.length > 0 && data.statusCounts && data.statusCounts.length > 0) {
                const resolvedColors = data.statusKeys.map((k, i) => {
                    const hex = parseColor(data.statusHex?.[i]);
                    return hex && hex !== '' ? hex : resolvedInk;
                });

                const statusCanvas = document.getElementById('statusChart');
                if (statusCanvas) {
                    window.__dashCharts.st = new Chart(statusCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: data.statusLabels,
                            datasets: [{
                                data: data.statusCounts,
                                backgroundColor: resolvedColors,
                                borderColor: resolvedColors,
                                borderWidth: 1,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: 12,
                                        padding: 10,
                                        usePointStyle: true,
                                        color: resolvedFontColor,
                                        generateLabels: (chart) => {
                                            const labels = chart.data.labels || [];
                                            const colors = chart.data.datasets[0]?.backgroundColor || [];
                                            return labels.map((label, i) => ({
                                                text: label,
                                                fillStyle: colors[i] || '#9ca3af',
                                                strokeStyle: colors[i] || '#9ca3af',
                                                lineWidth: 1,
                                                pointStyle: 'circle',
                                                hidden: false,
                                                index: i,
                                                fontColor: resolvedFontColor
                                            }));
                                        }
                                    }
                                }
                            },
                            cutout: '68%',
                        }
                    });
                }
            }
        };

        if (!window.__dashCharts.mo) {
            window.__dashCharts.mo = new MutationObserver((mutations) => {
                const hasThemeChange = mutations.some(m => m.attributeName === 'class'
                    || m.attributeName === 'data-theme');
                if (hasThemeChange && window.__dashCharts._lastData) {
                    window.renderDashboardCharts(window.__dashCharts._lastData);
                }
            });
            window.__dashCharts.mo.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class', 'data-theme']
            });
        }

        if (window.Livewire) {
            window.addEventListener('livewire:navigating', function() {
                if (window.__dashCharts) {
                    if (window.__dashCharts.s) window.__dashCharts.s.destroy();
                    if (window.__dashCharts.st) window.__dashCharts.st.destroy();
                    if (window.__dashCharts.mo) window.__dashCharts.mo.disconnect();
                    window.__dashCharts = {};
                }
            });
        }
    </script>
</div>
