<?php

namespace App\Filament\Admin\Resources\MasterFinishingResource\Pages;

use App\Filament\Admin\Resources\MasterFinishingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterFinishing extends EditRecord
{
    protected static string $resource = MasterFinishingResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (empty($data['proses_list']) && ! empty($this->record->proses_finishing)) {
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
