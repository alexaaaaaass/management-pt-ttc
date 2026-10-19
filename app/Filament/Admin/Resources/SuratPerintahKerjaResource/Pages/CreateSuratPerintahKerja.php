<?php

namespace App\Filament\Admin\Resources\SuratPerintahKerjaResource\Pages;

use App\Filament\Admin\Resources\SuratPerintahKerjaResource;
use App\Services\Production\ProductionService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSuratPerintahKerja extends CreateRecord
{
    protected static string $resource = SuratPerintahKerjaResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(ProductionService::class)->createSpk($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
