<?php

namespace App\Filament\Admin\Resources\GajiResource\Pages;

use App\Filament\Admin\Resources\GajiResource;
use App\Services\HR\PayrollService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateGaji extends CreateRecord
{
    protected static string $resource = GajiResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(PayrollService::class)->recordPayroll($data);
    }
}
