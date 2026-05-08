<?php

namespace App\Filament\Admin\Resources\PracetakProcessResource\Pages;

use App\Filament\Admin\Resources\PracetakProcessResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPracetakProcess extends EditRecord
{
    protected static string $resource = PracetakProcessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
