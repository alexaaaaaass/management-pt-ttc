<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ErrorProductionResource\Pages;
use App\Models\ErrorProduction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ErrorProductionResource extends Resource
{
    protected static ?string $model = ErrorProduction::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'Printing';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Master Note Waste';

    protected static ?string $modelLabel = 'Master Note Waste';

    protected static ?string $pluralModelLabel = 'Master Note Waste';

    public static function suggestKode(): string
    {
        $last = ErrorProduction::where('kode_error', 'like', 'WST-%')
            ->orderByDesc('kode_error')
            ->value('kode_error');

        $next = $last ? ((int) substr($last, 4)) + 1 : 1;

        return 'WST-' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode_error')
                    ->label('Kode Waste')
                    ->default(fn () => static::suggestKode())
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\TextInput::make('nama_error')
                    ->label('Nama Note Waste')
                    ->placeholder('Contoh: CETAK MIRING')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('kode_error')
                    ->label('Kode Waste')
                    ->badge()
                    ->color('warning')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_error')
                    ->label('Nama Note Waste')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
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
            'index' => Pages\ListErrorProductions::route('/'),
            'create' => Pages\CreateErrorProduction::route('/create'),
            'edit' => Pages\EditErrorProduction::route('/{record}/edit'),
        ];
    }
}