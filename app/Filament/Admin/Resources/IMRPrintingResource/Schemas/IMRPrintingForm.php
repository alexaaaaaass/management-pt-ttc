<?php

namespace App\Filament\Admin\Resources\IMRPrintingResource\Schemas;

use Filament\Forms\Form;

class IMRPrintingForm
{
    public static function make(Form $form): Form
    {
        return $form
            ->schema([

                HeaderSection::make(),

                LoadBomAction::make(),

                ItemsRepeater::make(),

            ]);
    }
}