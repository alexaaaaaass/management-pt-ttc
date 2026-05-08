@if($this->prItems && $this->prItems->count())

<div class="overflow-x-auto rounded-xl border border-gray-700">
    <table class="w-full table-fixed text-sm text-left">
        <thead class="bg-gray-800 text-white">
            <tr>
                <th class="w-16 px-4 py-3 text-center">No</th>
                <th class="px-4 py-3">Item</th>
                <th class="w-24 px-4 py-3 text-center">Qty</th>
                <th class="w-32 px-4 py-3 text-center">Satuan</th>
                <th class="w-40 px-4 py-3 text-center">ETA</th>
                <th class="w-48 px-4 py-3">Catatan</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($this->prItems ?? [] as $index => $item)
                <tr class="border-t border-gray-700">
                    <td class="px-4 py-3 text-center">
                        {{ $index + 1 }}
                    </td>

                    <td class="px-4 py-3 font-semibold">
                        {{ $item->item->nama_master_item ?? '-' }}
                    </td>

                    <td class="px-4 py-3 text-center">
                        {{ $item->qty }}
                    </td>

                    <td class="px-4 py-3 text-center">
                        {{ $item->satuan }}
                    </td>

                    <td class="px-4 py-3 text-center">
                        {{ $item->eta }}
                    </td>

                    <td class="px-4 py-3">
                        {{ $item->catatan ?? '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@else

<div class="text-gray-400 mt-3">
    Belum ada item dari Purchase Request
</div>

@endif