@php($fieldClass = 'mt-1 block w-full rounded border-gray-300 text-sm')
<div class="grid gap-5 sm:grid-cols-2">
    <label class="text-sm font-medium text-gray-700">No. BA
        <input name="no_ba" value="{{ old('no_ba', $noBa) }}" readonly class="{{ $fieldClass }} bg-gray-50">
        @error('no_ba')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm font-medium text-gray-700">Tipe
        <select name="tipe" required class="{{ $fieldClass }}">
            @foreach (['complaint_produksi' => 'Complaint Produksi', 'rapat' => 'Rapat'] as $value => $label)
                <option value="{{ $value }}" @selected(old('tipe', $beritaAcara?->tipe ?? 'complaint_produksi') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('tipe')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm font-medium text-gray-700">Tanggal
        <input type="date" name="tanggal" value="{{ old('tanggal', $beritaAcara?->tanggal?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required class="{{ $fieldClass }}">
        @error('tanggal')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm font-medium text-gray-700">Surat Jalan
        <select name="surat_jalan_id" class="{{ $fieldClass }}"><option value="">Pilih Surat Jalan</option>
            @foreach ($suratJalans as $suratJalan)<option value="{{ $suratJalan->id }}" @selected((string) old('surat_jalan_id', $beritaAcara?->surat_jalan_id) === (string) $suratJalan->id)>{{ $suratJalan->kode_surat_jalan }}</option>@endforeach
        </select>
        @error('surat_jalan_id')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm font-medium text-gray-700">Invoice
        <select name="invoice_id" class="{{ $fieldClass }}"><option value="">Pilih Invoice</option>
            @foreach ($invoices as $invoice)<option value="{{ $invoice->id }}" @selected((string) old('invoice_id', $beritaAcara?->invoice_id) === (string) $invoice->id)>{{ $invoice->kode_invoice }}</option>@endforeach
        </select>
        @error('invoice_id')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm font-medium text-gray-700">Customer
        <select name="customer_id" class="{{ $fieldClass }}"><option value="">Pilih customer</option>
            @foreach ($customers as $customer)<option value="{{ $customer->id }}" @selected((string) old('customer_id', $beritaAcara?->customer_id) === (string) $customer->id)>{{ $customer->nama_customer }}</option>@endforeach
        </select>
        @error('customer_id')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm font-medium text-gray-700">No. Surat Jalan / Invoice
        <input name="no_surat_jalan_invoice" value="{{ old('no_surat_jalan_invoice', $beritaAcara?->no_surat_jalan_invoice) }}" class="{{ $fieldClass }}">
        @error('no_surat_jalan_invoice')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm font-medium text-gray-700">Item / Produk
        <input name="item_product" value="{{ old('item_product', $beritaAcara?->item_product) }}" class="{{ $fieldClass }}">
        @error('item_product')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm font-medium text-gray-700 sm:col-span-2">Foto Bukti (maks. 10 file, 5 MB per file)
        <input type="file" name="foto_bukti[]" accept="image/*" multiple class="{{ $fieldClass }}">
        @error('foto_bukti')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
        @error('foto_bukti.*')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
        @if ($beritaAcara?->foto_bukti)
            <div class="mt-3 flex flex-wrap gap-3">@foreach ($beritaAcara->foto_bukti as $photo)<a href="{{ Storage::disk('public')->url($photo) }}" target="_blank" rel="noopener"><img src="{{ Storage::disk('public')->url($photo) }}" alt="Foto bukti" class="h-20 w-20 rounded object-cover"></a>@endforeach</div>
        @endif
    </label>
    @foreach (['kronologi' => 'Kronologi', 'analisa_produksi' => 'Analisa Produksi', 'tindakan_solusi' => 'Tindakan / Solusi', 'peserta' => 'Peserta', 'agenda' => 'Agenda', 'catatan' => 'Catatan'] as $field => $label)
        <label class="text-sm font-medium text-gray-700 sm:col-span-2">{{ $label }}
            <textarea name="{{ $field }}" rows="3" class="{{ $fieldClass }}">{{ old($field, $beritaAcara?->{$field}) }}</textarea>
            @error($field)<span class="text-xs text-red-600">{{ $message }}</span>@enderror
        </label>
    @endforeach
    <label class="text-sm font-medium text-gray-700">Tanda Tangan QC
        <input name="ttd_qc" value="{{ old('ttd_qc', $beritaAcara?->ttd_qc) }}" class="{{ $fieldClass }}">
        @error('ttd_qc')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm font-medium text-gray-700">Tanda Tangan Customer
        <input name="ttd_customer" value="{{ old('ttd_customer', $beritaAcara?->ttd_customer) }}" class="{{ $fieldClass }}">
        @error('ttd_customer')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm font-medium text-gray-700">Status
        <select name="status" required class="{{ $fieldClass }}">
            @foreach (['draft' => 'Draft', 'proses' => 'Proses', 'selesai' => 'Selesai'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $beritaAcara?->status ?? 'draft') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
    </label>
</div>