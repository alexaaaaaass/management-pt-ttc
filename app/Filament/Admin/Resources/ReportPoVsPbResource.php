<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ReportPoVsPbResource\Pages;
use App\Filament\Admin\Resources\ReportPoVsPbResource\RelationManagers;
use App\Models\ReportPoVsPb;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReportPoVsPbResource extends Resource
{
    protected static ?string $model = ReportPoVsPb::class;
    protected static ?string $navigationGroup = 'Report';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function getNavigationLabel(): string
{
    return 'Report PO VS PB';
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReportPoVsPbs::route('/'),
            'create' => Pages\CreateReportPoVsPb::route('/create'),
            'edit' => Pages\EditReportPoVsPb::route('/{record}/edit'),
        ];
    }
}
