<div x-data="{
    chartDays: <?php echo e(json_encode($salesByDay->pluck('date')->values())); ?>,
    chartRevenue: <?php echo e(json_encode($salesByDay->pluck('revenue')->values()->map(fn($v) => (float) $v))); ?>,
    chartOrders: <?php echo e(json_encode($salesByDay->pluck('total')->values()->map(fn($v) => (int) $v))); ?>,
    statusLabels: <?php echo e(json_encode($ordersByStatus->pluck('key')->values())); ?>,
    statusKeys: <?php echo e(json_encode($ordersByStatus->pluck('key')->values())); ?>,
    statusCounts: <?php echo e(json_encode($ordersByStatus->pluck('count')->values()->map(fn($v) => (int) $v))); ?>,
    statusColors: <?php echo e(json_encode($ordersByStatus->pluck('color')->values())); ?>,
    stateLabels: <?php echo e(json_encode($ordersByState->pluck('name')->values())); ?>,
    stateCounts: <?php echo e(json_encode($ordersByState->pluck('count')->values()->map(fn($v) => (int) $v))); ?>,
    stateRevenues: <?php echo e(json_encode($ordersByState->pluck('revenue')->values()->map(fn($v) => (float) $v))); ?>,
    deliveryLabels: <?php echo e(json_encode($deliveryTypeBreakdown->pluck('delivery_type')->values())); ?>,
    deliveryCounts: <?php echo e(json_encode($deliveryTypeBreakdown->pluck('count')->values()->map(fn($v) => (int) $v))); ?>,
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
}" x-init="renderCharts()" x-destroy="if (window.__dashCharts) { window.__dashCharts.s?.destroy?.(); window.__dashCharts.st?.destroy?.(); window.__dashCharts.mo?.disconnect?.(); window.__dashCharts = {}; }">

    <div class="edz-stagger grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
        
        <div class="lg:col-span-2 edz-card edz-card--padded">
            <h3 class="text-sm font-semibold tracking-tight text-ink mb-4"><?php echo e(__('dashboard.sales_trend')); ?></h3>
            <div class="h-64">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        
        <div class="edz-card edz-card--padded">
            <h3 class="text-sm font-semibold tracking-tight text-ink mb-4"><?php echo e(__('dashboard.orders_by_status')); ?></h3>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ordersByStatus->isNotEmpty()): ?>
                <div class="h-64">
                    <canvas id="statusChart"></canvas>
                </div>
            <?php else: ?>
                <div class="h-64 flex items-center justify-center">
                    <p class="text-sm text-ink-muted"><?php echo e(__('dashboard.no_data')); ?></p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <script>
        window.__dashCharts = window.__dashCharts || {};
        window.renderDashboardCharts = function(data) {
            const root = getComputedStyle(document.documentElement);
            const fontColor = root.getPropertyValue('--edz-color-text-soft')?.trim() || '#6b7280';
            const gridColor = root.getPropertyValue('--edz-color-border')?.trim() || '#e5e7eb';
            const accent500 = root.getPropertyValue('--edz-color-accent-500')?.trim() || '#6366f1';
            const success500 = root.getPropertyValue('--edz-color-success-500')?.trim() || '#22c55e';
            const inkColor = root.getPropertyValue('--edz-color-ink')?.trim() || '#111827';

            const toRgba = (hex, alpha = 0.08) => {
                if (!hex) return `rgba(107, 114, 128, ${alpha})`;
                let c = hex.trim();
                if (c.startsWith('rgb')) return c.replace('rgb', 'rgba').replace(')', `, ${alpha})`);
                if (c.startsWith('rgba')) return c.replace(/,[^,]+\)$/, `, ${alpha})`);
                if (c[0] === '#') c = c.substring(1);
                if (c.length === 3) c = c[0]+c[0]+c[1]+c[1]+c[2]+c[2];
                const r = parseInt(c.substring(0,2), 16);
                const g = parseInt(c.substring(2,4), 16);
                const b = parseInt(c.substring(4,6), 16);
                if (Number.isNaN(r)) return `rgba(107, 114, 128, ${alpha})`;
                return `rgba(${r}, ${g}, ${b}, ${alpha})`;
            };

            Chart.defaults.color = fontColor;
            Chart.defaults.font.family = "'Inter', 'IBM Plex Sans Arabic', sans-serif";

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
                                return dt.toLocaleDateString('ar-DZ', { month: 'short', day: 'numeric' });
                            }),
                            datasets: [{
                                label: '<?php echo e(__("dashboard.revenue")); ?>',
                                data: data.chartRevenue,
                                borderColor: accent500,
                                backgroundColor: toRgba(accent500, 0.08),
                                fill: true,
                                tension: 0.4,
                                borderWidth: 2,
                                pointRadius: 0,
                                pointHoverRadius: 5,
                                pointHoverBackgroundColor: accent500,
                                yAxisID: 'y',
                            }, {
                                label: '<?php echo e(__("dashboard.total_orders")); ?>',
                                data: data.chartOrders,
                                borderColor: success500,
                                backgroundColor: toRgba(success500, 0.05),
                                fill: false,
                                tension: 0.4,
                                borderWidth: 2,
                                pointRadius: 0,
                                pointHoverRadius: 5,
                                pointHoverBackgroundColor: success500,
                                yAxisID: 'y1',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { intersect: false, mode: 'index' },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: { boxWidth: 12, padding: 16, usePointStyle: true }
                                }
                            },
                            scales: {
                                x: { grid: { color: gridColor, drawBorder: false }, border: { display: false } },
                                y: {
                                    position: 'left',
                                    grid: { color: gridColor, drawBorder: false },
                                    border: { display: false },
                                    title: { display: true, text: '<?php echo e(__("stores.currency_symbol")); ?>' }
                                },
                                y1: {
                                    position: 'right',
                                    grid: { drawOnChartArea: false },
                                    border: { display: false },
                                    title: { display: true, text: '<?php echo e(__("dashboard.orders")); ?>' }
                                },
                            }
                        }
                    });
                }
            }

            if (data.statusKeys && data.statusKeys.length > 0 && data.statusCounts && data.statusCounts.length > 0) {
                const resolvedColors = data.statusKeys.map((k) => {
                    const nk = (k || '').toString().trim().toLowerCase();
                    if (nk === 'pending') {
                        return '#9ca3af';
                    }
                    return inkColor;
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
                                borderWidth: 0,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: { boxWidth: 12, padding: 10, usePointStyle: true }
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
                const rootEl = document.documentElement;
                const hasThemeChange = mutations.some(m => m.attributeName === 'class' || m.attributeName === 'data-theme');
                if (hasThemeChange && window.__dashCharts._lastData) {
                    window.renderDashboardCharts(window.__dashCharts._lastData);
                }
            });
            window.__dashCharts.mo.observe(document.documentElement, { attributes: true, attributeFilter: ['class', 'data-theme'] });
        }

        window.addEventListener('render-dashboard-charts-data', (e) => {
            if (e && e.detail) {
                window.__dashCharts._lastData = e.detail;
            }
        }, { once: true });
    </script>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views/livewire/merchant/dashboard/partials/charts.blade.php ENDPATH**/ ?>