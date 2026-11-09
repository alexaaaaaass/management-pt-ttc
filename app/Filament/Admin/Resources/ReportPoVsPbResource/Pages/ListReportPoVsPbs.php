<?php

namespace App\Filament\Admin\Resources\ReportPoVsPbResource\Pages;

use App\Filament\Admin\Resources\ReportPoVsPbResource;
use Filament\Resources\Pages\ListRecords;

class ListReportPoVsPbs extends ListRecords
{
    protected static string $resource = ReportPoVsPbResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
