<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
                Pilih file Excel COA. Sistem akan memeriksa kode akun dan nama akun
                sebelum menyimpan akun baru.
            </p>

            <form wire:submit="preview" class="space-y-4">
                {{ $this->form }}

                <x-filament::button type="submit" icon="heroicon-o-eye">
                    Pratinjau Excel
                </x-filament::button>
            </form>
        </div>

        @if ($hasPreview)
            @php
                $newCount = collect($previewRows)->where('status', 'Baru')->count();
                $existingCount = collect($previewRows)->where('status', 'Sudah ada')->count();
                $invalidCount = collect($previewRows)
                    ->whereIn('status', ['Tidak valid', 'Duplikat dalam Excel'])
                    ->count();
            @endphp

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-xl border p-4">
                    <p class="text-sm text-gray-500">Akun baru</p>
                    <p class="text-2xl font-bold text-success-600">{{ $newCount }}</p>
                </div>

                <div class="rounded-xl border p-4">
                    <p class="text-sm text-gray-500">Sudah ada</p>
                    <p class="text-2xl font-bold">{{ $existingCount }}</p>
                </div>

                <div class="rounded-xl border p-4">
                    <p class="text-sm text-gray-500">Tidak valid / duplikat</p>
                    <p class="text-2xl font-bold text-danger-600">{{ $invalidCount }}</p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="p-3 text-left">Baris Excel</th>
                            <th class="p-3 text-left">Kode Akun</th>
                            <th class="p-3 text-left">Nama Akun</th>
                            <th class="p-3 text-left">Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($previewRows as $row)
                            <tr wire:key="coa-row-{{ $loop->index }}" class="border-t">
                                <td class="p-3">{{ $row['row'] }}</td>
                                <td class="p-3">{{ $row['code'] }}</td>
                                <td class="p-3">{{ $row['name'] }}</td>
                                <td class="p-3">{{ $row['status'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap gap-3">
                <x-filament::button
                    color="success"
                    icon="heroicon-o-check-circle"
                    wire:click="import"
                    wire:confirm="Yakin ingin menyimpan akun baru dari hasil pratinjau ini?"
                    :disabled="$newCount === 0"
                >
                    Konfirmasi &amp; Import Akun Baru
                </x-filament::button>
            </div>
        @endif
    </div>
</x-filament-panels::page>