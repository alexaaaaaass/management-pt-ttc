<?php

namespace App\Filament\Admin\Resources\ImrFinishResource\Schemas;

use Filament\Forms\Form;

class ImrFinishForm
{
    public static function make(Form $form): Form
    {
        return $form->schema([
            HeaderSection::make(),
            ItemsRepeater::make(),
        ]);
    }
}