<div class="space-y-6 font-['Inter',sans-serif]"
     x-data="{
        year: @entangle('year'),
        salesData: @js($salesMonthly),
        purchaseData: @js($purchaseMonthly),
        months: @js($months),
        salesChartInstance: null,
        purchaseChartInstance: null,

        init() {
            this.loadChartJs(() => {
                this.$nextTick(() => {
                    this.renderCharts();
                });
            });

            Livewire.on('dashboard-data-updated', (data) => {
                let payload = Array.isArray(data) ? data[0] : data;
                if (payload) {
                    this.salesData = payload.sales || this.salesData;
                    this.purchaseData = payload.purchase || this.purchaseData;
                    this.months = payload.months || this.months;
                    this.updateCharts();
                }
            });
        },

        loadChartJs(callback) {
            if (window.Chart) {
                callback();
                return;
            }
            let script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            script.onload = () => callback();
            document.head.appendChild(script);
        },

        renderCharts() {
            if (!window.Chart) return;

            // 1. Sales Chart
            const ctxSales = this.$refs.salesCanvas;
            if (ctxSales) {
                if (this.salesChartInstance) this.salesChartInstance.destroy();
                this.salesChartInstance = new Chart(ctxSales, {
                    type: 'bar',
                    data: {
                        labels: this.months,
                        datasets: [{
                            label: 'Penjualan (IDR)',
                            data: this.salesData,
                            backgroundColor: '#4f46e5',
                            hoverBackgroundColor: '#4338ca',
                            borderRadius: 6,
                            borderSkipped: false,
                            maxBarThickness: 36,
                        }]
                    },
                    options: this.getChartOptions('#4f46e5')
                });
            }

            // 2. Purchase Chart
            const ctxPurchase = this.$refs.purchaseCanvas;
            if (ctxPurchase) {
                if (this.purchaseChartInstance) this.purchaseChartInstance.destroy();
                this.purchaseChartInstance = new Chart(ctxPurchase, {
                    type: 'bar',
                    data: {
                        labels: this.months,
                        datasets: [{
                            label: 'Pembelian (IDR)',
                            data: this.purchaseData,
                            backgroundColor: '#f43f5e',
                            hoverBackgroundColor: '#e11d48',
                            borderRadius: 6,
                            borderSkipped: false,
                            maxBarThickness: 36,
                        }]
                    },
                    options: this.getChartOptions('#f43f5e')
                });
            }
        },

        updateCharts() {
            if (this.salesChartInstance) {
                this.salesChartInstance.data.datasets[0].data = this.salesData;
                this.salesChartInstance.update();
            }
            if (this.purchaseChartInstance) {
                this.purchaseChartInstance.data.datasets[0].data = this.purchaseData;
                this.purchaseChartInstance.update();
            }
        },

        getChartOptions(accentColor) {
            return {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#ffffff',
                        bodyColor: '#cbd5e1',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                let val = context.raw || 0;
                                return ' Rp ' + new Intl.NumberFormat('id-ID').format(val);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#94a3b8',
                            font: { size: 12, family: 'Inter', weight: 500 }
                        }
                    },
                    y: {
                        border: { dash: [4, 4] },
                        grid: { color: 'rgba(226, 232, 240, 0.6)' },
                        ticks: {
                            color: '#94a3b8',
                            font: { size: 11, family: 'Inter' },
                            callback: function(value) {
                                if (value >= 1000000000) return (value / 1000000000).toFixed(1) + 'B';
                                if (value >= 1000000) return (value / 1000000).toFixed(0) + 'M';
                                return value;
                            }
                        }
                    }
                }
            };
        }
     }">

    <!-- ========================================== -->
    <!-- HEADER SECTION (Title, Subtitle, Filter)   -->
    <!-- ========================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-1">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
                <span>Dashboard</span>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                Dashboard
            </h1>
            <p class="text-sm font-normal text-gray-500 dark:text-gray-400 mt-0.5">
                Monitoring performa operasional tahun <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $year }}</span>
            </p>
        </div>

        <!-- Filter Tahun -->
        <div class="flex items-center gap-2 self-start sm:self-auto bg-white dark:bg-gray-900 p-1.5 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="relative flex items-center">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                </svg>
                <input
                    type="number"
                    wire:model.defer="year"
                    min="2000"
                    max="2099"
                    class="pl-9 pr-2 py-1.5 w-24 text-sm font-semibold text-gray-800 dark:text-gray-200 bg-transparent border-0 focus:ring-0 focus:outline-none"
                    placeholder="Tahun"
                >
            </div>
            <button
                type="button"
                wire:click="searchYear"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold rounded-lg shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <span>Cari</span>
            </button>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- ROW 1: 4 KPI CARDS (BIO FORMAT)            -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- 1. TOTAL ORDER -->
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    TOTAL ORDER
                </span>
                <div class="text-3xl font-extrabold text-gray-900 dark:text-white mt-1.5 tracking-tight font-['Inter']">
                    {{ $kpis['total_order'] }}
                </div>
            </div>
            <div class="w-13 h-13 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-md shadow-indigo-200 dark:shadow-none shrink-0 p-3">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
            </div>
        </div>

        <!-- 2. TOTAL KIRIM -->
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    TOTAL KIRIM
                </span>
                <div class="text-3xl font-extrabold text-gray-900 dark:text-white mt-1.5 tracking-tight font-['Inter']">
                    {{ $kpis['total_kirim'] }}
                </div>
            </div>
            <div class="w-13 h-13 rounded-2xl bg-amber-500 flex items-center justify-center text-white shadow-md shadow-amber-200 dark:shadow-none shrink-0 p-3">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V3.75m0 3.75h3.75" />
                </svg>
            </div>
        </div>

        <!-- 3. TOTAL PO -->
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    TOTAL PO
                </span>
                <div class="text-3xl font-extrabold text-gray-900 dark:text-white mt-1.5 tracking-tight font-['Inter']">
                    {{ $kpis['total_po'] }}
                </div>
            </div>
            <div class="w-13 h-13 rounded-2xl bg-emerald-600 flex items-center justify-center text-white shadow-md shadow-emerald-200 dark:shadow-none shrink-0 p-3">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
            </div>
        </div>

        <!-- 4. TOTAL PB -->
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    TOTAL PB
                </span>
                <div class="text-3xl font-extrabold text-gray-900 dark:text-white mt-1.5 tracking-tight font-['Inter']">
                    {{ $kpis['total_pb'] }}
                </div>
            </div>
            <div class="w-13 h-13 rounded-2xl bg-rose-500 flex items-center justify-center text-white shadow-md shadow-rose-200 dark:shadow-none shrink-0 p-3">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                </svg>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- ROW 2: 2 BAR CHARTS SIDE-BY-SIDE           -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Chart 1: Statistik Penjualan (IDR) -->
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center gap-2 mb-4">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white tracking-tight">
                    Statistik Penjualan (IDR)
                </h3>
            </div>
            <div class="h-64 relative w-full" wire:ignore>
                <canvas x-ref="salesCanvas"></canvas>
            </div>
        </div>

        <!-- Chart 2: Statistik Pembelian (IDR) -->
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center gap-2 mb-4">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white tracking-tight">
                    Statistik Pembelian (IDR)
                </h3>
            </div>
            <div class="h-64 relative w-full" wire:ignore>
                <canvas x-ref="purchaseCanvas"></canvas>
            </div>
        </div>
    </div>
</div>
