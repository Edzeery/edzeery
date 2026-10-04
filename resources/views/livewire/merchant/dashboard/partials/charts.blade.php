<div wire:key="dash-charts-{{ ($filter ?? null)?->hash() ?? '' }}" x-data="{
    chartDays: {{ json_encode($salesSeries['labels'] ?? $salesByDay->pluck('date')->values()) }},
    chartRevenue: {{ json_encode($salesSeries['revenue'] ?? $salesByDay->pluck('revenue')->values()->map(fn($v) => (float) $v)) }},
    chartOrders: {{ json_encode($salesSeries['orders'] ?? $salesByDay->pluck('orders')->values()->map(fn($v) => (int) $v)) }},
    statusLabels: {{ json_encode($ordersByStatus->pluck('label')->values()) }},
    statusKeys: {{ json_encode($ordersByStatus->pluck('key')->values()) }},
    statusCounts: {{ json_encode($ordersByStatus->pluck('count')->values()->map(fn($v) => (int) $v)) }},
    statusHex: {{ json_encode($ordersByStatus->pluck('hex')->values()) }},
    stateLabels: {{ json_encode($ordersByState->pluck('name')->values()) }},
    stateCounts: {{ json_encode($ordersByState->pluck('count')->values()->map(fn($v) => (int) $v)) }},
    stateRevenues: {{ json_encode($ordersByState->pluck('revenue')->values()->map(fn($v) => (float) $v)) }},
    deliveryLabels: {{ json_encode($deliveryTypeBreakdown->pluck('delivery_type')->values()) }},
    deliveryCounts: {{ json_encode($deliveryTypeBreakdown->pluck('count')->values()->map(fn($v) => (int) $v)) }},
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
            <h3 class="text-sm font-semibold tracking-tight text-ink mb-4">{{ __('dashboard.sales_trend') }}</h3>
            <div class="h-64">
                <canvas id="salesChart"></canvas>
            </div>
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
            if (!window.__dashCharts) window.__dashCharts = {};
            window.__dashCharts._lastData = data;
            const root = getComputedStyle(document.documentElement);
            const cssVar = (name) => root.getPropertyValue(name)?.trim() || null;

            const toRgbFromTriplet = (triplet) => {
                if (!triplet) return null;
                const t = triplet.replace(/[^0-9,\s]/g, '').trim();
                if (!t.includes(',')) return null;
                const parts = t.split(',').map(p => parseInt(p.trim(), 10));
                if (parts.length < 3 || parts.some(isNaN)) return null;
                return `rgb(${parts[0]}, ${parts[1]}, ${parts[2]})`;
            };

            const parseColor = (input) => {
                if (!input) return null;
                let c = String(input).trim();
                return c;
            };

            const toRgba = (input, alpha = 0.08) => {
                const c = parseColor(input);
                if (!c) return `rgba(107, 114, 128, ${alpha})`;
                if (c.startsWith('rgb(') && !c.startsWith('rgba(')) return c.replace('rgb(', 'rgba(').replace(')',
                    `, ${alpha})`);
                if (c.startsWith('rgba(')) {
                    return c.replace(/,[^,]+\)$/, `, ${alpha})`);
                }
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

            let fontColor = cssVar('--edz-color-text-soft');
            let gridColor = cssVar('--edz-color-border');
            let accent500 = cssVar('--edz-color-accent-500');
            let success500 = cssVar('--edz-color-success-500');

            fontColor = toRgbFromTriplet(fontColor) || fontColor || '#6b7280';
            gridColor = toRgbFromTriplet(gridColor) || gridColor || '#e5e7eb';
            accent500 = toRgbFromTriplet(accent500) || accent500 || '#6366f1';
            success500 = toRgbFromTriplet(success500) || success500 || '#22c55e';

            const resolvedFontColor = parseColor(fontColor) || fontColor;
            const resolvedGridColor = parseColor(gridColor) || gridColor;
            const resolvedAccent = parseColor(accent500) || accent500;
            const resolvedSuccess = parseColor(success500) || success500;
            Chart.defaults.color = resolvedFontColor;
            Chart.defaults.font.family = "'Inter', 'IBM Plex Sans Arabic', sans-serif";
            if (Chart.defaults.plugins.legend && Chart.defaults.plugins.legend.labels) {
                Chart.defaults.plugins.legend.labels.color = resolvedFontColor;
            }
            if (Chart.defaults.plugins.tooltip) {
                Chart.defaults.plugins.tooltip.titleColor = resolvedFontColor;
                Chart.defaults.plugins.tooltip.bodyColor = resolvedFontColor;
            }

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
                            labels: data.chartDays.map(d => {
                                const dt = new Date(d);
                                return dt.toLocaleDateString('ar-DZ', {
                                    month: 'short',
                                    day: 'numeric'
                                });
                            }),
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
                                    grid: {
                                        color: resolvedGridColor,
                                        drawBorder: false
                                    },
                                    border: {
                                        display: false
                                    },
                                    ticks: {
                                        color: resolvedFontColor
                                    }
                                },
                                y: {
                                    position: 'left',
                                    grid: {
                                        color: resolvedGridColor,
                                        drawBorder: false
                                    },
                                    border: {
                                        display: false
                                    },
                                    ticks: {
                                        color: resolvedFontColor
                                    },
                                    title: {
                                        display: true,
                                        text: @js(__('stores.currency_symbol')),
                                        color: resolvedFontColor
                                    }
                                },
                                y1: {
                                    position: 'right',
                                    grid: {
                                        drawOnChartArea: false
                                    },
                                    border: {
                                        display: false
                                    },
                                    ticks: {
                                        color: resolvedFontColor
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
                    const hex = data.statusHex?.[i];
                    if (hex && String(hex).trim() !== '') {
                        const parsed = parseColor(hex);
                        if (parsed) return parsed;
                    }
                    const fallbackInk = cssVar('--edz-color-ink');
                    const inkRgb = toRgbFromTriplet(fallbackInk) || fallbackInk || '#111827';
                    return inkRgb;
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
                                            const dataset = chart.data.datasets[0] || {};
                                            const colors = dataset.backgroundColor || [];
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
                const hasThemeChange = mutations.some(m => m.attributeName === 'class' || m.attributeName ===
                    'data-theme');
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
