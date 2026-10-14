<?php

namespace App\Filament\Admin\Resources\PoBillingResource\Pages;

use App\Filament\Admin\Resources\PoBillingResource;
use App\Services\Finance\PoBillingService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPoBilling extends EditRecord
{
    protected static string $resource = PoBillingResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(PoBillingService::class)->updateBilling($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(fn ($record) => !app(PoBillingService::class)->canDelete($record))
                ->using(fn ($record) => app(PoBillingService::class)->deleteBilling($record)),
        ];
    }
}
