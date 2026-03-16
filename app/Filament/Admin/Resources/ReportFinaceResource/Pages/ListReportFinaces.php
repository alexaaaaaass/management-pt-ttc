<?php

namespace App\Filament\Admin\Resources\ReportFinaceResource\Pages;

use App\Filament\Admin\Resources\ReportFinaceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReportFinaces extends ListRecords
{
    protected static string $resource = ReportFinaceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
