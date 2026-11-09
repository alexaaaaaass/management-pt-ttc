<?php

namespace App\Filament\Admin\Resources\MasterOperatorResource\Pages;

use App\Filament\Admin\Resources\MasterOperatorResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterOperator extends CreateRecord
{
    protected static string $resource = MasterOperatorResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
