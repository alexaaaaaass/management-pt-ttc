<x-filament-panels::page>

   

    @php
        $karyawans = \App\Models\Karyawan::all();
    @endphp

    <table class="w-full">
        <thead>
            <tr>
                <th class="text-left p-2">Nama Karyawan</th>
                <th class="text-left p-2">Total Cuti</th>
                <th class="text-left p-2">Digunakan</th>
                <th class="text-left p-2">Sisa</th>
            </tr>
        </thead>

        <tbody>

        @foreach($karyawans as $karyawan)

            @php

            $digunakan = \App\Models\Cuti::query()
    ->where('karyawan_id', $karyawan->id)
    ->sum('jumlah_hari');

                $sisa = max(0, 12 - $digunakan);

            @endphp

            <tr>
                <td class="p-2">
                    {{ $karyawan->nama }}
                </td>

                <td class="p-2">
                    12 Hari
                </td>

                <td class="p-2">
                    {{ $digunakan }} Hari
                </td>

                <td class="p-2 text-success-600">
                    {{ $sisa }} Hari
                </td>
            </tr>

        @endforeach

        </tbody>
    </table>

</x-filament-panels::page>