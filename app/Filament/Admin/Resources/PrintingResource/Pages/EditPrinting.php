<?php

namespace App\Filament\Admin\Resources\PrintingResource\Pages;

use App\Filament\Admin\Resources\PrintingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPrinting extends EditRecord
{
    protected static string $resource = PrintingResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (empty($data['proses_list']) && ! empty($this->record->proses_printing)) {
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
