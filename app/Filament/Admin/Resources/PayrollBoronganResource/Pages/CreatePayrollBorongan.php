<?php

namespace App\Filament\Admin\Resources\PayrollBoronganResource\Pages;

use App\Filament\Admin\Resources\PayrollBoronganResource;
use App\Services\HR\PayrollService;
use Filament\Resources\Pages\CreateRecord;

class CreatePayrollBorongan extends CreateRecord
{
    protected static string $resource = PayrollBoronganResource::class;

    protected function afterCreate(): void
    {
        app(PayrollService::class)->recalculateBorongan($this->record);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
