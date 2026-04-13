<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ReportFinaceResource\Pages;
use App\Filament\Admin\Resources\ReportFinaceResource\RelationManagers;
use App\Models\ReportFinace;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReportFinaceResource extends Resource
{
    protected static ?string $model = ReportFinace::class;
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
    return 'Report Finance';
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
            'index' => Pages\ListReportFinaces::route('/'),
            'create' => Pages\CreateReportFinace::route('/create'),
            'edit' => Pages\EditReportFinace::route('/{record}/edit'),
        ];
    }
}
