<?php

namespace App\Filament\Admin\Resources\HrShiftResource\Pages;

use App\Filament\Admin\Resources\HrShiftResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHrShift extends EditRecord
{
    protected static string $resource = HrShiftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
