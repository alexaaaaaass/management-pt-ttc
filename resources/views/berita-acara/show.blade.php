<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-gray-800">{{ $beritaAcara->no_ba }}</h2>
            <div class="flex gap-2"><a href="{{ route('berita-acaras.edit', $beritaAcara) }}" class="rounded bg-indigo-600 px-4 py-2 text-sm text-white">Edit</a><a href="{{ route('berita-acaras.index') }}" class="rounded border px-4 py-2 text-sm">Kembali</a></div>
        </div>
    </x-slot>
    <div class="py-8"><div class="mx-auto max-w-4xl space-y-5 px-4 sm:px-6 lg:px-8">
        @if (session('success'))<div class="rounded bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div>@endif
        <div class="rounded bg-white p-6 shadow-sm">
            <dl class="grid gap-5 sm:grid-cols-2">
                @foreach (['tipe' => 'Tipe', 'tanggal' => 'Tanggal', 'customer.nama_customer' => 'Customer', 'no_surat_jalan_invoice' => 'No. Surat Jalan / Invoice', 'item_product' => 'Item / Produk', 'status' => 'Status', 'kronologi' => 'Kronologi', 'analisa_produksi' => 'Analisa Produksi', 'tindakan_solusi' => 'Tindakan / Solusi', 'peserta' => 'Peserta', 'agenda' => 'Agenda', 'catatan' => 'Catatan', 'ttd_qc' => 'Tanda Tangan QC', 'ttd_customer' => 'Tanda Tangan Customer'] as $field => $label)
                    @php($value = data_get($beritaAcara, $field))
                    <div class="{{ in_array($field, ['kronologi', 'analisa_produksi', 'tindakan_solusi', 'peserta', 'agenda', 'catatan'], true) ? 'sm:col-span-2' : '' }}"><dt class="text-xs font-semibold uppercase text-gray-500">{{ $label }}</dt><dd class="mt-1 whitespace-pre-line text-sm text-gray-900">{{ $field === 'tanggal' ? $value?->format('d/m/Y') : ($value ?: '-') }}</dd></div>
                @endforeach
            </dl>
            @if ($beritaAcara->foto_bukti)
                <div class="mt-6"><h3 class="text-sm font-semibold text-gray-700">Foto Bukti</h3><div class="mt-3 flex flex-wrap gap-3">@foreach ($beritaAcara->foto_bukti as $photo)<a href="{{ Storage::disk('public')->url($photo) }}" target="_blank" rel="noopener"><img src="{{ Storage::disk('public')->url($photo) }}" alt="Foto bukti" class="h-28 w-28 rounded object-cover"></a>@endforeach</div></div>
            @endif
            <form method="POST" action="{{ route('berita-acaras.destroy', $beritaAcara) }}" class="mt-8 border-t pt-5" onsubmit="return confirm('Hapus berita acara ini?')">
                @csrf
                @method('DELETE')
                <button class="text-sm font-medium text-red-700">Hapus Berita Acara</button>
            </form>
        </div>
    </div></div>
</x-app-layout>