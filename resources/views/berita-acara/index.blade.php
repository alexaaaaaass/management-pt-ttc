<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold text-gray-800">Berita Acara Complaint Produksi</h2>
            <a href="{{ route('berita-acaras.create') }}" class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white">Buat Berita Acara</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div>
            @endif
            <form method="GET" class="flex flex-wrap gap-2">
                <input name="search" value="{{ $search }}" placeholder="No. BA atau customer" class="min-w-64 rounded border-gray-300 text-sm">
                <select name="status" class="rounded border-gray-300 text-sm">
                    <option value="">Semua status</option>
                    @foreach (['draft' => 'Draft', 'proses' => 'Proses', 'selesai' => 'Selesai'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="rounded border border-gray-300 px-4 py-2 text-sm">Cari</button>
            </form>
            <div class="overflow-x-auto rounded bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr><th class="px-4 py-3">No. BA</th><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Customer</th><th class="px-4 py-3">No. SJ/Invoice</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Aksi</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($beritaAcaras as $beritaAcara)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 font-medium">{{ $beritaAcara->no_ba }}</td>
                                <td class="whitespace-nowrap px-4 py-3">{{ $beritaAcara->tanggal->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">{{ $beritaAcara->customer?->nama_customer ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $beritaAcara->no_surat_jalan_invoice ?? '-' }}</td>
                                <td class="px-4 py-3 capitalize">{{ $beritaAcara->status }}</td>
                                <td class="whitespace-nowrap px-4 py-3"><a class="text-indigo-700 hover:underline" href="{{ route('berita-acaras.show', $beritaAcara) }}">Detail</a> <a class="ml-3 text-indigo-700 hover:underline" href="{{ route('berita-acaras.edit', $beritaAcara) }}">Edit</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada berita acara.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $beritaAcaras->links() }}
        </div>
    </div>
</x-app-layout>