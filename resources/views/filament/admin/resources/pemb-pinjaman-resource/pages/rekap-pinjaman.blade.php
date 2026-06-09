<x-filament-panels::page>

    <div class="overflow-x-auto bg-white rounded-lg shadow">

        <table class="w-full">

            <thead>
                <tr class="border-b font-semibold">
                    <th class="p-3 text-left">Nama Karyawan</th>
                    <th class="p-3 text-left">Total Pinjaman</th>
                    <th class="p-3 text-left">Total Pembayaran</th>
                    <th class="p-3 text-left">Sisa Pinjaman</th>
                    <th class="p-3 text-left">Status</th>
                </tr>
            </thead>

            <tbody>

                @foreach ($this->data as $item)

                    @php
                        $totalPembayaran = $item->pembayaran->sum('nominal_pembayaran');

                        $sisa = $item->nilai_pinjaman - $totalPembayaran;
                    @endphp

                    <tr class="border-b">

                        <td class="p-3">
                            {{ $item->karyawan?->nama }}
                        </td>

                        <td class="p-3">
                            Rp {{ number_format($item->nilai_pinjaman,0,',','.') }}
                        </td>

                        <td class="p-3">
                            Rp {{ number_format($totalPembayaran,0,',','.') }}
                        </td>

                        <td class="p-3">
                            Rp {{ number_format($sisa,0,',','.') }}
                        </td>

                        <td class="p-3">

                            @if($sisa <= 0)

                                <span class="text-green-600 font-bold">
                                    Lunas
                                </span>

                            @else

                                <span class="text-red-600 font-bold">
                                    Belum Lunas
                                </span>

                            @endif

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    </div>

</x-filament-panels::page>