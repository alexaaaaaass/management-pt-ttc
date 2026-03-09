<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MasterFinishingResource\Pages;
use App\Models\MasterFinishing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class MasterFinishingResource extends Resource
{
    protected static ?string $model = MasterFinishing::class;
     protected static ?string $navigationGroup = 'Finishing';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

     public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_mesin')
                    ->label('Nama Mesin')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('jenis_mesin')
                    ->label('Jenis Mesin')
                    ->required(),

                Forms\Components\TextInput::make('kapasitas')
                    ->numeric()
                    ->label('Kapasitas'),

                Forms\Components\TextInput::make('proses')
                    ->label('Proses'),

                Forms\Components\Toggle::make('status')
                    ->label('Status Mesin')
                    ->onColor('success')
                    ->offColor('danger')
                    ->default(true),
            ])
            ->columns(2);
    }

   public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_mesin')
                    ->searchable(),

                Tables\Columns\TextColumn::make('jenis_mesin')
                    ->searchable(),

                Tables\Columns\TextColumn::make('kapasitas'),

                Tables\Columns\TextColumn::make('proses'),

                Tables\Columns\IconColumn::make('status')
                    ->boolean()
                    ->label('Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListMasterFinishings::route('/'),
            'create' => Pages\CreateMasterFinishing::route('/create'),
            'edit' => Pages\EditMasterFinishing::route('/{record}/edit'),
        ];
    }
}