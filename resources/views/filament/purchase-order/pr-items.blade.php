@if($this->prItems && $this->prItems->count())

<div class="mt-4 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">

<table class="w-full text-sm">

<thead class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-200">
<tr>
<th class="px-3 py-2">No</th>
<th class="px-3 py-2">Item</th>
<th class="px-3 py-2">Qty</th>
<th class="px-3 py-2">Satuan</th>
<th class="px-3 py-2">ETA</th>
<th class="px-3 py-2">Catatan</th>
</tr>
</thead>

<tbody class="divide-y divide-gray-200 dark:divide-gray-700">

@foreach($this->prItems as $i => $item)

<tr class="hover:bg-gray-50 dark:hover:bg-gray-800">

<td class="px-3 py-2">
{{ $i+1 }}
</td>

<td class="px-3 py-2 font-medium">
{{ $item->item->nama_master_item ?? '-' }}
</td>

<td class="px-3 py-2">
{{ number_format($item->qty, 0) }}
</td>

<td class="px-3 py-2">
{{ $item->satuan }}
</td>

<td class="px-3 py-2">
{{ $item->eta }}
</td>

<td class="px-3 py-2 text-gray-500">
{{ $item->catatan }}
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