<x-filament-panels::page>
    @php
        $recap = $this->getRecap();
        $depts = $this->getDepartemens();
        $tipes = $this->getTipeOptions();
        $bulanNama = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
        $pdfUrl = route('hrd.rekap-absensi.preview', [
            'tahun' => $this->tahun,
            'bulan' => $this->bulan,
            'departemen_id' => $this->departemenId,
            'tipe_gaji' => $this->tipeGaji,
        ]);
    @endphp

    <div class="mb-4 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-wrap items-end justify-between gap-3">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Bulan</label>
                <select wire:model.live="bulan" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm py-1.5 pl-3 pr-9" style="min-width:130px;padding-right:2.25rem;">
                    @foreach($bulanNama as $num => $nm)
                        <option value="{{ $num }}">{{ $nm }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Tahun</label>
                <select wire:model.live="tahun" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm py-1.5 pl-3 pr-9" style="min-width:92px;padding-right:2.25rem;">
                    @foreach(range(now()->year - 2, now()->year + 1) as $yr)
                        <option value="{{ $yr }}">{{ $yr }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Departemen</label>
                <select wire:model.live="departemenId" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm py-1.5 pl-3 pr-9" style="min-width:170px;padding-right:2.25rem;">
                    <option value="">Semua Departemen</option>
                    @foreach($depts as $d)
                        <option value="{{ $d->id }}">{{ $d->nama_departemen }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Skema Gaji</label>
                <select wire:model.live="tipeGaji" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm py-1.5 pl-3 pr-9" style="min-width:150px;padding-right:2.25rem;">
                    <option value="">Semua Skema</option>
                    @foreach($tipes as $k => $v)
                        <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ \App\Filament\Admin\Resources\AbsensiResource::getUrl() }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600">
                ← Kembali ke Log Absensi
            </a>
            <a href="{{ $pdfUrl }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg bg-primary-600 text-white hover:bg-primary-700">
                Cetak PDF Rekap Absensi
            </a>
        </div>
    </div>

    <div class="mb-3 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-600 dark:text-gray-300">
        <div>
            Periode Cut-off Aktif:
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200">
                {{ $recap['cutoff_label'] ?? '-' }}
            </span>
        </div>
        <div class="text-gray-400">Menghitung shift masing-masing karyawan, hari libur nasional, dan catatan scorsing.</div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-7 gap-3 mb-4">
        <div class="bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500">Hari Kerja Efektif</div>
            <div class="text-xl font-bold text-gray-900 dark:text-white">{{ $recap['hari_kerja'] }} Hari</div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500">Total Hadir (OK)</div>
            <div class="text-xl font-bold text-emerald-600">{{ $recap['summary']['hadir'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500">Terlambat</div>
            <div class="text-xl font-bold text-amber-600">{{ $recap['summary']['telat'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500">Izin / Sakit / Cuti</div>
            <div class="text-xl font-bold text-blue-600">{{ $recap['summary']['izin'] + $recap['summary']['sakit'] + $recap['summary']['cuti'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500">Alpha</div>
            <div class="text-xl font-bold text-red-600">{{ $recap['summary']['alpha'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500">Scorsing</div>
            <div class="text-xl font-bold text-rose-600">{{ $recap['summary']['scorsing'] ?? 0 }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-3 rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500">Total Lembur</div>
            <div class="text-xl font-bold text-indigo-600">{{ $recap['summary']['lembur'] }} Jam</div>
        </div>
    </div>

    <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs uppercase text-gray-600 dark:text-gray-300">
                    <th class="p-3 text-left">Karyawan</th>
                    <th class="p-3 text-left">Dept / Shift / Skema</th>
                    <th class="p-3 text-center">Hari Kerja</th>
                    <th class="p-3 text-center">Hadir</th>
                    <th class="p-3 text-center">Telat</th>
                    <th class="p-3 text-center">P. Cepat</th>
                    <th class="p-3 text-center">Izin</th>
                    <th class="p-3 text-center">Sakit</th>
                    <th class="p-3 text-center">Cuti</th>
                    <th class="p-3 text-center">Alpha</th>
                    <th class="p-3 text-center">Scorsing</th>
                    <th class="p-3 text-center">Lembur (Jam)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($recap['rows'] as $row)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                        <td class="p-3 font-semibold text-gray-900 dark:text-white">
                            {{ $row['nama'] }}
                            <div class="text-xs font-normal text-gray-400">NIP: {{ $row['nip'] ?: '-' }}</div>
                        </td>
                        <td class="p-3 text-xs text-gray-600 dark:text-gray-300">
                            {{ $row['departemen'] }}
                            <div class="text-gray-400">{{ $row['shift'] ?? 'Default' }} · {{ $row['tipe_gaji'] }}</div>
                        </td>
                        <td class="p-3 text-center">{{ $row['hari_kerja'] }}</td>
                        <td class="p-3 text-center font-bold text-emerald-600">{{ $row['hadir'] }}</td>
                        <td class="p-3 text-center {{ $row['telat'] > 0 ? 'text-amber-600 font-bold' : 'text-gray-400' }}">{{ $row['telat'] }}</td>
                        <td class="p-3 text-center {{ $row['pulang_cepat'] > 0 ? 'text-amber-600' : 'text-gray-400' }}">{{ $row['pulang_cepat'] }}</td>
                        <td class="p-3 text-center">{{ $row['izin'] }}</td>
                        <td class="p-3 text-center">{{ $row['sakit'] }}</td>
                        <td class="p-3 text-center text-blue-600">{{ $row['cuti'] }}</td>
                        <td class="p-3 text-center {{ $row['alpha'] > 0 ? 'text-red-600 font-bold' : 'text-gray-400' }}">{{ $row['alpha'] }}</td>
                        <td class="p-3 text-center {{ ($row['scorsing'] ?? 0) > 0 ? 'text-rose-600 font-bold' : 'text-gray-400' }}">{{ $row['scorsing'] ?? 0 }}</td>
                        <td class="p-3 text-center font-semibold text-indigo-600">{{ $row['lembur_jam'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="p-6 text-center text-gray-400">Belum ada data karyawan aktif pada filter ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
