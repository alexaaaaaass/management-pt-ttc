<?php

namespace App\Filament\Admin\Resources\CategoryItemResource\Pages;

use App\Filament\Admin\Resources\CategoryItemResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCategoryItem extends CreateRecord
{
    protected static string $resource = CategoryItemResource::class;
}
