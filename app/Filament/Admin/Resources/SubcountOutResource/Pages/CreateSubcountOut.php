<?php

namespace App\Filament\Admin\Resources\SubcountOutResource\Pages;

use App\Filament\Admin\Resources\SubcountOutResource;
use App\Services\Subcontract\SubcontractService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSubcountOut extends CreateRecord
{
    protected static string $resource = SubcountOutResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(SubcontractService::class)->createSubcountOut($data);
    }
}