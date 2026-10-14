<?php

namespace App\Filament\Admin\Resources\SubcountInResource\Pages;

use App\Filament\Admin\Resources\SubcountInResource;
use App\Services\Subcontract\SubcontractService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSubcountIn extends CreateRecord
{
    protected static string $resource = SubcountInResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(SubcontractService::class)->createSubcountIn($data);
    }
}
