<?php

namespace App\Filament\Admin\Resources\ReportFinaceResource\Pages;

use App\Filament\Admin\Resources\ReportFinaceResource;
use Filament\Resources\Pages\ListRecords;

class ListReportFinaces extends ListRecords
{
    protected static string $resource = ReportFinaceResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}