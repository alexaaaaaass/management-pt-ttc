<?php

namespace App\Filament\Admin\Resources\PackagingResource\Pages;

use App\Filament\Admin\Resources\PackagingResource;
use App\Services\Production\ProductionService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPackaging extends EditRecord
{
    protected static string $resource = PackagingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->using(fn (Model $record) => app(ProductionService::class)->deletePackaging($record)),
        ];
    }
}
