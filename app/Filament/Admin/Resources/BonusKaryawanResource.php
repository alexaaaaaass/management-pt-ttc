<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BonusKaryawanResource\Pages;
use App\Models\BonusKaryawan;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Tables\Actions\BulkActionGroup;
use Tables\Actions\DeleteAction;
use Tables\Actions\EditAction;

class BonusKaryawanResource extends Resource
{
    protected static ?string $model = BonusKaryawan::class;
    protected static ?string $navigationGroup = 'HRD';
    protected static ?int $navigationSort = 9;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('kode_gudang')
                    ->label('Kode Gudang')
                    ->required(),

                Select::make('karyawan_id')
                    ->label('Karyawan')
                    ->relationship('karyawan', 'nama_lengkap')
                    ->searchable()
                    ->required(),

                DatePicker::make('tanggal_bonus')
                    ->label('Tanggal Bonus')
                    ->required(),

                TextInput::make('nilai_bonus')
                    ->label('Nilai Bonus')
                    ->numeric()
                    ->required(),

                Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_gudang')
                    ->label('Kode Gudang')
                    ->searchable(),

                TextColumn::make('karyawan.nama_lengkap')
                    ->label('Karyawan')
                    ->searchable(),

                TextColumn::make('tanggal_bonus')
                    ->label('Tanggal Bonus')
                    ->date(),

                TextColumn::make('nilai_bonus')
                    ->label('Nilai Bonus')
                    ->money('IDR'),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(50),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBonusKaryawans::route('/'),
            'create' => Pages\CreateBonusKaryawan::route('/create'),
            'edit' => Pages\EditBonusKaryawan::route('/{record}/edit'),
        ];
    }
}