<?php

namespace App\Filament\Admin\Resources\SuratPerintahKerjaResource\Pages;

use App\Filament\Admin\Resources\SuratPerintahKerjaResource;
use App\Services\Production\ProductionService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditSuratPerintahKerja extends EditRecord
{
    protected static string $resource = SuratPerintahKerjaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->using(fn (Model $record) => app(ProductionService::class)->deleteSpk($record)),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(ProductionService::class)->updateSpk($record, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
