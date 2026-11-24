<?php

namespace App\Filament\Admin\Resources\OperatorDiemakingResource\Pages;

use App\Filament\Admin\Resources\OperatorDiemakingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOperatorDiemaking extends CreateRecord
{
    protected static string $resource = OperatorDiemakingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
