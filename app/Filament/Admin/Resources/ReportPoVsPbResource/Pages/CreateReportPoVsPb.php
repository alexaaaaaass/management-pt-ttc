<?php

namespace App\Filament\Admin\Resources\ReportPoVsPbResource\Pages;

use App\Filament\Admin\Resources\ReportPoVsPbResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateReportPoVsPb extends CreateRecord
{
    protected static string $resource = ReportPoVsPbResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
