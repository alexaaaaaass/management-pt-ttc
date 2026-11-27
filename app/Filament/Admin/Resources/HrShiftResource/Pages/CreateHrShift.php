<?php

namespace App\Filament\Admin\Resources\HrShiftResource\Pages;

use App\Filament\Admin\Resources\HrShiftResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHrShift extends CreateRecord
{
    protected static string $resource = HrShiftResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
