<?php

namespace App\Filament\Admin\Resources\PracetakMaterialMovementResource\Pages;

use App\Filament\Admin\Resources\PracetakMaterialMovementResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPracetakMaterialMovement extends ViewRecord
{
    protected static string $resource = PracetakMaterialMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
