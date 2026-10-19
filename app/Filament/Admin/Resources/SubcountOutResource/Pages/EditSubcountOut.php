<?php

namespace App\Filament\Admin\Resources\SubcountOutResource\Pages;

use App\Filament\Admin\Resources\SubcountOutResource;
use App\Services\Subcontract\SubcontractService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubcountOut extends EditRecord
{
    protected static string $resource = SubcountOutResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['items'] = $this->record->items->toArray();

        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        $record->update($data);

        $record->items()->delete();
        foreach ($items as $item) {
            $record->items()->create($item);
        }

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(fn ($record) => !app(SubcontractService::class)->canDeleteSubcountOut($record))
                ->using(fn ($record) => app(SubcontractService::class)->deleteSubcountOut($record)),
        ];
    }
}
