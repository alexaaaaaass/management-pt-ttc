<?php

namespace App\Filament\Admin\Resources\SuratJalanResource\Pages;

use App\Filament\Admin\Resources\SuratJalanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSuratJalan extends EditRecord
{
    protected static string $resource = SuratJalanResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->loadMissing('salesOrder');
        $data['order_type'] = $this->record->salesOrder?->mode;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        $canModify = app(\App\Services\Dispatch\SuratJalanService::class)->canModify($this->record);

        return [
            Actions\DeleteAction::make()
                ->disabled(! $canModify)
                ->tooltip(! $canModify ? 'Surat Jalan tidak dapat dihapus karena sudah memiliki Invoice.' : null),
        ];
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $detailItems = $data['items'] ?? [];
        unset($data['items']);

        return app(\App\Services\Dispatch\SuratJalanService::class)->updateSuratJalan($record, $data, $detailItems);
    }
}
