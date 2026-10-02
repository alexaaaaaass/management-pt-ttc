<?php

namespace App\Filament\Admin\Resources\IMRPrintingResource\Pages;

use App\Filament\Admin\Resources\IMRPrintingResource;
use App\Services\IMR\IMRPrintingService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateIMRPrinting extends CreateRecord
{
    protected static string $resource = IMRPrintingResource::class;

   protected function handleRecordCreation(array $data): Model
{
    return app(IMRPrintingService::class)
        ->create($data);
}
}