<?php

namespace App\Filament\Admin\Resources\JurnalMemorialResource\Pages;

use App\Filament\Admin\Resources\JurnalMemorialResource;
use App\Services\Finance\TreasuryService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateJurnalMemorial extends CreateRecord
{
    protected static string $resource = JurnalMemorialResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(TreasuryService::class)->recordJournalMemorial($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

