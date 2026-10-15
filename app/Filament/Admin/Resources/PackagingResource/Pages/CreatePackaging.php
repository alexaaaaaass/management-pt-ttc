<?php

namespace App\Filament\Admin\Resources\PackagingResource\Pages;

use App\Filament\Admin\Resources\PackagingResource;
use App\Services\Production\ProductionService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePackaging extends CreateRecord
{
    protected static string $resource = PackagingResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(ProductionService::class)->recordPackaging($data);
    }
}
