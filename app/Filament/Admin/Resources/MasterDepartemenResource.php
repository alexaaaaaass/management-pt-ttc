<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MasterDepartemenResource\Pages;
use App\Models\MasterDepartemen;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;


class MasterDepartemenResource extends Resource
{
    protected static ?string $navigationGroup = 'HRD';
    protected static ?string $navigationLabel = 'Departemen';
    protected static ?int $navigationSort = 1;
    protected static ?string $model = MasterDepartemen::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';

  public static function form(Form $form): Form
{
    return $form
        ->schema([
            TextInput::make('kode')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(20),

            TextInput::make('nama_departemen')
                ->required()
                ->maxLength(255),

            Textarea::make('deskripsi')
                ->rows(3)
                ->columnSpanFull(),

            Toggle::make('status')
                ->label('Status Aktif')
                ->default(true),
        ]);
}
   public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('kode')
                ->searchable()
                ->sortable(),

            TextColumn::make('nama_departemen')
                ->searchable()
                ->sortable(),

            TextColumn::make('deskripsi')
                ->limit(30),

            IconColumn::make('status')
                ->boolean(),

            TextColumn::make('created_at')
                ->dateTime('d M Y'),
        ])
        ->filters([
            Tables\Filters\TernaryFilter::make('status')
                ->label('Status')
                ->boolean(),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListMasterDepartemens::route('/'),
            'create' => Pages\CreateMasterDepartemen::route('/create'),
            'edit' => Pages\EditMasterDepartemen::route('/{record}/edit'),
        ];
    }
}