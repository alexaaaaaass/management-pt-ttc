<?php

namespace App\Filament\Admin\Resources\ReportProduksiResource\Pages;

use App\Filament\Admin\Resources\ReportProduksiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReportProduksis extends ListRecords
{
    protected static string $resource = ReportProduksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
