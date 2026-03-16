<?php

namespace App\Filament\Admin\Resources\PoBillPayResource\Pages;

use App\Filament\Admin\Resources\PoBillPayResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPoBillPay extends EditRecord
{
    protected static string $resource = PoBillPayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
