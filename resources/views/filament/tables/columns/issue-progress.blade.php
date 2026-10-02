@php

$approved = $getRecord()->items->sum('qty_approved');

$issued = $getRecord()->items->sum('qty_issued');

$percent = $approved > 0
    ? ($issued / $approved) * 100
    : 0;

@endphp

<div class="space-y-2">

    <div class="text-xs">
        {{ number_format($issued,2) }}
        /
        {{ number_format($approved,2) }}
    </div>

    <div class="w-full bg-gray-200 rounded-full h-2">

        <div
            class="bg-primary-600 h-2 rounded-full"
            style="width: {{ $percent }}%">
        </div>

    </div>

    <div class="text-xs font-semibold">
        {{ number_format($percent,2) }}%
    </div>

</div>