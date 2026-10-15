<?php

namespace App\Filament\Admin\Resources\GajiResource\Pages;

use App\Filament\Admin\Resources\GajiResource;
use App\Services\HR\PayrollService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditGaji extends EditRecord
{
    protected static string $resource = GajiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->using(fn ($record) => app(PayrollService::class)->deletePayroll($record)),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(PayrollService::class)->updatePayroll($record, $data);
    }
}
