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
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BonusKaryawanResource extends Resource
{
    protected static ?string $model = BonusKaryawan::class;
    protected static ?string $navigationGroup = 'HRD';
    protected static ?int $navigationSort = 9;
    protected static ?string $navigationIcon = 'heroicon-o-gift';

    public static function getNavigationLabel(): string
    {
        return 'Bonus Karyawan';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('kode_gudang')
                    ->label('Kode Gudang')
                    ->default('UGRMS')
                    ->required(),

                Select::make('karyawan_id')
                    ->label('Karyawan')
                    ->relationship('karyawan', 'nama')
                    ->searchable()
                    ->preload()
                    ->required(),

                DatePicker::make('tanggal_bonus')
                    ->label('Tanggal Bonus')
                    ->default(now())
                    ->required(),

                TextInput::make('periode')
                    ->label('Periode (YYYY-MM)')
                    ->default(now()->format('Y-m'))
                    ->placeholder('2026-10')
                    ->required(),

                TextInput::make('nilai_bonus')
                    ->label('Nilai Bonus')
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(1)
                    ->required(),

                Textarea::make('keterangan')
                    ->label('Keterangan / Alasan Bonus')
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('karyawan.nama')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('periode')
                    ->label('Periode')
                    ->badge(),

                TextColumn::make('tanggal_bonus')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('nilai_bonus')
                    ->label('Nilai Bonus')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('kode_gudang')
                    ->label('Gudang'),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
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
            'index'  => Pages\ListBonusKaryawans::route('/'),
            'create' => Pages\CreateBonusKaryawan::route('/create'),
            'edit'   => Pages\EditBonusKaryawan::route('/{record}/edit'),
        ];
    }
}