<?php

namespace App\Filament\Admin\Resources;

use App\Models\Blokir;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Admin\Resources\BlokirResource\Pages;

class BlokirResource extends Resource
{
    protected static ?string $model = Blokir::class;
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Dispatch';

    protected static ?string $navigationIcon = 'heroicon-o-no-symbol';

    protected static ?string $navigationLabel = 'Blokir';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('no_blokir')
                    ->label('No. Blokir')
                    ->required(),

                TextInput::make('no_spk')
                    ->label('No. SPK')
                    ->required(),

                DatePicker::make('tanggal_blokir')
                    ->label('Tanggal Blokir')
                    ->required(),

                TextInput::make('operator')
                    ->required(),

                TextInput::make('qty_blokir')
                    ->label('Qty Blokir')
                    ->numeric()
                    ->required(),

                TextInput::make('customer')
                    ->required(),

                Textarea::make('keterangan')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_blokir')
                    ->label('No. Blokir')
                    ->searchable(),

                TextColumn::make('no_spk')
                    ->label('No. SPK')
                    ->searchable(),

                TextColumn::make('tanggal_blokir')
                    ->label('Tanggal Blokir')
                    ->date(),

                TextColumn::make('operator'),

                TextColumn::make('qty_blokir')
                    ->label('Qty Blokir'),

                TextColumn::make('customer'),

                TextColumn::make('keterangan')
                    ->limit(50),
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
            'index' => Pages\ListBlokirs::route('/'),
            'create' => Pages\CreateBlokir::route('/create'),
            'edit' => Pages\EditBlokir::route('/{record}/edit'),
        ];
    }
}