<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MetodeBayarResource\Pages;
use App\Models\MetodeBayar;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MetodeBayarResource extends Resource
{
    protected static ?string $model = MetodeBayar::class;
    protected static ?string $navigationGroup = 'Finance';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
     protected static ?int $navigationSort = 5;
    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('kode_pembayaran')
                ->label('Kode Pembayaran')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(50),

            Forms\Components\TextInput::make('metode_pembayaran')
                ->label('Metode Pembayaran')
                ->required()
                ->maxLength(100),
        ]);
}

    public static function getNavigationLabel(): string
{
    return 'Metode Bayar';
}
    

   public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('kode_pembayaran')
                ->label('Kode')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('metode_pembayaran')
                ->label('Metode')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Dibuat')
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
            'index' => Pages\ListMetodeBayars::route('/'),
            'create' => Pages\CreateMetodeBayar::route('/create'),
            'edit' => Pages\EditMetodeBayar::route('/{record}/edit'),
        ];
    }
}