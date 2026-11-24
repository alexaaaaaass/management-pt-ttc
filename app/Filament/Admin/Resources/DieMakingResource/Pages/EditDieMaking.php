<?php

namespace App\Filament\Admin\Resources\DieMakingResource\Pages;

use App\Filament\Admin\Resources\DieMakingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDieMaking extends EditRecord
{
    protected static string $resource = DieMakingResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (empty($data['proses_list']) && ! empty($this->record->proses_diemaking)) {
            $data['proses_list'] = $this->record->proses_labels;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncWasteSummary();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
