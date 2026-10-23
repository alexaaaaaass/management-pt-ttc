<?php

namespace App\Filament\Admin\Resources\FinishGoodItemResource\Pages;

use App\Filament\Admin\Resources\FinishGoodItemResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFinishGoodItem extends CreateRecord
{
    protected static string $resource = FinishGoodItemResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
