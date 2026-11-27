<?php

namespace App\Filament\Admin\Resources\NpbSettlementResource\Pages;

use App\Filament\Admin\Resources\NpbSettlementResource;
use App\Models\NpbRequest;
use App\Services\Finance\NpbService;
use App\Support\MoneyHelper;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateNpbSettlement extends CreateRecord
{
    protected static string $resource = NpbSettlementResource::class;

    protected function afterFill(): void
    {
        $npbId = request()->query('npb_request_id');
        if (! $npbId) {
            return;
        }

        $npb = NpbRequest::with('items')->find((int) $npbId);
        if (! $npb) {
            return;
        }

        $danaCair = (float) $npb->nominal_dicairkan > 0
            ? round((float) $npb->nominal_dicairkan + (float) $npb->total_dana_tersedia, 2)
            : round((float) $npb->total_kebutuhan_utama, 2);

        $this->form->fill([
            'nomor_penyelesaian'   => 'AUTO',
            'npb_request_id'       => $npb->id,
            'tanggal_penyelesaian' => now()->toDateString(),
            'periode_realisasi'    => 'UTAMA',
            'label_periode'        => $npb->periode_utama,
            'total_dana_dicairkan' => $danaCair,
            'tindak_lanjut_sisa'   => 'CARRY_OVER',
            'posting_mode'         => 'MANUAL_LINK_ONLY',
            'status'               => 'DRAFT',
            'disusun_oleh'         => 'Bagian Keuangan',
            'diperiksa_oleh'       => 'Kepala',
            'disetujui_oleh'       => 'Owner',
        ]);

        // Set repeater items setelah form->fill() agar loadStateFromRelationships() tidak mengosongkannya
        $items = app(NpbService::class)->buildSettlementItemsFromNpb($npb, 'UTAMA');
        $keyed = [];
        foreach ($items as $row) {
            $row['nominal_anggaran'] = MoneyHelper::format($row['nominal_anggaran'], 2);
            $row['nominal_realisasi'] = MoneyHelper::format($row['nominal_realisasi'], 2);
            $keyed[(string) Str::uuid()] = $row;
        }
        $this->data['items'] = $keyed;
    }

    protected function afterCreate(): void
    {
        app(NpbService::class)->recalculateSettlement($this->record);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
