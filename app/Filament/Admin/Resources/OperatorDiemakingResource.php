<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OperatorDiemakingResource\Pages;
use App\Models\OperatorDiemaking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OperatorDiemakingResource extends Resource
{
    protected static ?string $model = OperatorDiemaking::class;

    protected static ?string $navigationGroup = 'Die Making';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationLabel = 'Operator Die Making';

    protected static ?string $modelLabel = 'Operator Die Making';

    protected static ?string $pluralModelLabel = 'Operator Die Making';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nama_operator')
                ->label('Nama Operator Die Making')
                ->required()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_operator')
                    ->label('Nama Operator')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOperatorDiemakings::route('/'),
            'create' => Pages\CreateOperatorDiemaking::route('/create'),
            'edit'   => Pages\EditOperatorDiemaking::route('/{record}/edit'),
        ];
    }
}
