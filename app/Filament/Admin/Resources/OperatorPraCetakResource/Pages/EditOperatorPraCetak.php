<?php

namespace App\Filament\Admin\Resources\OperatorPraCetakResource\Pages;

use App\Filament\Admin\Resources\OperatorPraCetakResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOperatorPraCetak extends EditRecord
{
    protected static string $resource = OperatorPraCetakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
