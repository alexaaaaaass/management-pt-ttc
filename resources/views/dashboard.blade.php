<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-6">

            <!-- JUDUL -->
            <h1 class="text-2xl font-bold text-gray-800 mb-6">
                📊 Dashboard Manajemen PT TTC
            </h1>

            <!-- RINGKASAN -->
            @php
                $cards = [
                    ['Produksi', 120, 'bg-green-500'],
                    ['Keuangan', 95, 'bg-blue-500'],
                    ['HRD', 75, 'bg-indigo-500'],
                    ['Gudang', 88, 'bg-yellow-500'],
                    ['Marketing', 102, 'bg-pink-500'],
                ];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
                @foreach ($cards as $card)
                    <div class="rounded-lg text-white p-4 {{ $card[2] }}">
                        <p class="text-sm">{{ $card[0] }}</p>
                        <p class="text-2xl font-bold">{{ $card[1] }}%</p>
                    </div>
                @endforeach
            </div>

            <!-- GRAFIK -->
            <div class="bg-white p-6 rounded-xl shadow">
                <h2 class="text-lg font-semibold mb-4">
                    Laporan Kinerja Tiap Divisi
                </h2>
                <canvas id="divisiChart" height="120"></canvas>
            </div>

        </div>
    </div>

    <!-- CHART JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('divisiChart'), {
            type: 'bar',
            data: {
                labels: ['Produksi', 'Keuangan', 'HRD', 'Gudang', 'Marketing'],
                datasets: [{
                    label: 'Persentase Laporan (%)',
                    data: [120, 95, 75, 88, 102],
                    backgroundColor: [
                        '#22c55e',
                        '#3b82f6',
                        '#6366f1',
                        '#eab308',
                        '#ec4899'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true }
                }
            }
        });
    </script>
</x-app-layout>
