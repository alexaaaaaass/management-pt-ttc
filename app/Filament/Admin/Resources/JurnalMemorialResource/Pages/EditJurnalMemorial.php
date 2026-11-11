<?php

namespace App\Filament\Admin\Resources\JurnalMemorialResource\Pages;

use App\Filament\Admin\Resources\JurnalMemorialResource;
use App\Services\Finance\TreasuryService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditJurnalMemorial extends EditRecord
{
    protected static string $resource = JurnalMemorialResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->loadMissing('lines');
        $data['lines'] = $this->record->lines->map(fn ($line) => [
            'coa_id'     => $line->coa_id,
            'keterangan' => $line->keterangan,
            'debit'      => (float) $line->debit,
            'kredit'     => (float) $line->kredit,
        ])->toArray();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(TreasuryService::class)->updateJournalMemorial($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview_pdf')
                ->label('Cetak Bukti PDF')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(fn () => route('finance.voucher.memorial.preview', $this->record))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make()
                ->using(fn ($record) => app(TreasuryService::class)->deleteJournalMemorial($record)),
        ];
    }
}

