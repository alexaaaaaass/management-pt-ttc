<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CutiResource\Pages;
use App\Models\Cuti;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class CutiResource extends Resource
{
    protected static ?string $model = Cuti::class;
    protected static ?int $navigationSort = 7;
    protected static ?string $navigationGroup = 'HRD';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function getModelLabel(): string
{
    return 'Cuti';
}

public static function getPluralModelLabel(): string
{
    return 'Cuti';
}


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('karyawan_id')
                    ->relationship('karyawan', 'nama_lengkap')
                    ->searchable()
                    ->required(),

                DatePicker::make('tanggal_cuti')
                    ->required(),

                Select::make('jenis_cuti')
                    ->options([
                        'Cuti Tahunan' => 'Cuti Tahunan',
                        'Cuti Khusus' => 'Cuti Khusus',
                    ])
                    ->required(),

                FileUpload::make('lampiran')
                    ->directory('cuti'),

                Textarea::make('keterangan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('karyawan.nama_lengkap')
                    ->label('Karyawan')
                    ->searchable(),

                TextColumn::make('tanggal_cuti')
                    ->label('Tanggal Cuti')
                    ->date(),

                TextColumn::make('jenis_cuti')
                    ->label('Jenis Cuti'),

                TextColumn::make('lampiran')
                    ->label('Lampiran')
                    ->formatStateUsing(fn () => 'Lihat'),

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
                    Tables\Actions\CreateAction::make()
                    ->label('Tambah Data Cuti')]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCutis::route('/'),
            'create' => Pages\CreateCuti::route('/create'),
            'edit' => Pages\EditCuti::route('/{record}/edit'),
        ];
    }
}