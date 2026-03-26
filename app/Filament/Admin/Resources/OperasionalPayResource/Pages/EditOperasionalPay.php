<?php

namespace App\Filament\Admin\Resources\OperasionalPayResource\Pages;

use App\Filament\Admin\Resources\OperasionalPayResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOperasionalPay extends EditRecord
{
    protected static string $resource = OperasionalPayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
