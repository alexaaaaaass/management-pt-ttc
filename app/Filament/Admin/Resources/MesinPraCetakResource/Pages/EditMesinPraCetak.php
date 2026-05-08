<?php

namespace App\Filament\Admin\Resources\MesinPraCetakResource\Pages;

use App\Filament\Admin\Resources\MesinPraCetakResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMesinPraCetak extends EditRecord
{
    protected static string $resource = MesinPraCetakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
