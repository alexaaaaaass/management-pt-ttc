<x-filament-panels::page>
    @php
        $counts = $this->getCounts();
        $items = $this->getItems();
        $tabs = [
            'cuti'     => 'Cuti',
            'izin'     => 'Izin, Sakit & Scorsing',
            'lembur'   => 'Lembur',
            'pinjaman' => 'Pinjaman Karyawan',
        ];
    @endphp

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 bg-white dark:bg-gray-800 p-3 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex flex-wrap gap-2">
            @foreach($tabs as $key => $label)
                <button type="button" wire:click="setTab('{{ $key }}')"
                    class="px-3.5 py-2 text-xs font-semibold rounded-lg transition {{ $this->activeTab === $key ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                    {{ $label }}
                    @if(($counts[$key] ?? 0) > 0)
                        <span class="ml-1 px-1.5 py-0.5 text-[10px] rounded-full bg-amber-500 text-white">{{ $counts[$key] }}</span>
                    @endif
                </button>
            @endforeach
        </div>

        <label class="inline-flex items-center gap-2 text-xs font-semibold text-gray-600 dark:text-gray-300 cursor-pointer">
            <input type="checkbox" wire:model.live="onlyPending" class="rounded border-gray-300 text-primary-600">
            Tampilkan hanya yang Pending
        </label>
    </div>

    <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs uppercase text-gray-600 dark:text-gray-300">
                    <th class="p-3 text-left">Karyawan</th>
                    <th class="p-3 text-left">Detail Pengajuan</th>
                    <th class="p-3 text-left">Keterangan</th>
                    <th class="p-3 text-center">Status</th>
                    <th class="p-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($items as $item)
                    @php
                        $st = $item->status ?: 'Approved';
                        $badgeCls = match ($st) {
                            'Approved' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
                            'Rejected' => 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300',
                            default    => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                        <td class="p-3 font-semibold text-gray-900 dark:text-white">
                            {{ $item->karyawan?->nama ?? '-' }}
                            <div class="text-xs font-normal text-gray-400">NIP: {{ $item->karyawan?->nip ?: '-' }}</div>
                        </td>
                        <td class="p-3 text-xs text-gray-700 dark:text-gray-200">
                            @if($this->activeTab === 'cuti')
                                <strong>{{ $item->jenis_cuti }}</strong> ({{ $item->jumlah_hari }} hari)<br>
                                {{ optional($item->tanggal_mulai)->format('d/m/Y') }} s/d {{ optional($item->tanggal_selesai)->format('d/m/Y') }}
                            @elseif($this->activeTab === 'izin')
                                <strong>{{ \App\Models\Izin::JENIS_OPTIONS[$item->jenis_izin] ?? $item->jenis_izin }}</strong><br>
                                Tgl: {{ optional($item->tanggal_izin)->format('d/m/Y') }}
                            @elseif($this->activeTab === 'lembur')
                                <strong>{{ $item->kode_lembur }}</strong> — {{ $item->durasi_lembur }} Jam<br>
                                Tgl: {{ optional($item->tanggal_lembur)->format('d/m/Y') }} ({{ $item->jam_mulai }}–{{ $item->jam_selesai }})
                            @else
                                <strong>{{ $item->nomor_bukti }}</strong> — Rp {{ number_format((float) $item->nilai_pinjaman, 0, ',', '.') }}<br>
                                Tenor {{ $item->jangka_waktu }} bln (Rp {{ number_format((float) $item->cicilan_per_bulan, 0, ',', '.') }}/bln)
                            @endif
                        </td>
                        <td class="p-3 text-xs text-gray-500 dark:text-gray-400">
                            {{ $this->activeTab === 'pinjaman' ? ($item->keperluan_pinjaman ?: '-') : ($item->keterangan ?: '-') }}
                        </td>
                        <td class="p-3 text-center">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeCls }}">{{ $st }}</span>
                            @if($item->approved_by)
                                <div class="text-[10px] text-gray-400 mt-0.5">{{ $item->approved_by }}</div>
                            @endif
                        </td>
                        <td class="p-3 text-right space-x-1 whitespace-nowrap">
                            @if($st !== 'Approved')
                                <button type="button" wire:click="approveItem('{{ $this->activeTab }}', {{ $item->id }})"
                                    class="px-2.5 py-1 text-xs font-semibold rounded-md bg-emerald-600 text-white hover:bg-emerald-700">
                                    Approve
                                </button>
                            @endif
                            @if($st !== 'Rejected')
                                <button type="button" wire:click="rejectItem('{{ $this->activeTab }}', {{ $item->id }})"
                                    class="px-2.5 py-1 text-xs font-semibold rounded-md bg-red-600 text-white hover:bg-red-700">
                                    Reject
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-gray-400">Tidak ada data pengajuan pada tab ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
