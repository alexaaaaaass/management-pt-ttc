<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OperatorDieMakingResource\Pages;
use App\Models\OperatorDieMaking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OperatorDieMakingResource extends Resource
{
    protected static ?string $model = OperatorDieMaking::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'Diemaking';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_operator')
                    ->label('Nama Operator')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_operator')
                    ->label('Nama Operator')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOperatorDieMakings::route('/'),
            'create' => Pages\CreateOperatorDieMaking::route('/create'),
            'edit' => Pages\EditOperatorDieMaking::route('/{record}/edit'),
        ];
    }
}