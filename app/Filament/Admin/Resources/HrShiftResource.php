<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\HrShiftResource\Pages;
use App\Models\HrShift;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HrShiftResource extends Resource
{
    protected static ?string $model = HrShift::class;
    protected static ?string $navigationGroup = 'HRD';
    protected static ?int $navigationSort = 2;
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Shift Kerja';
    protected static ?string $modelLabel = 'Shift Kerja';
    protected static ?string $pluralModelLabel = 'Master Shift Kerja';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Konfigurasi Jam Shift Kerja (Kantor & Produksi)')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('kode')
                            ->label('Kode Shift')
                            ->placeholder('SHIFT-1 / NON-SHIFT')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(30),

                        TextInput::make('nama')
                            ->label('Nama Shift')
                            ->placeholder('Shift 1 (Pagi)')
                            ->required()
                            ->maxLength(100),
                    ]),

                    Grid::make(3)->schema([
                        TextInput::make('jam_masuk')
                            ->label('Jam Masuk (HH:MM)')
                            ->placeholder('08:00')
                            ->required()
                            ->maxLength(5),

                        TextInput::make('jam_pulang')
                            ->label('Jam Pulang (HH:MM)')
                            ->placeholder('17:00')
                            ->required()
                            ->maxLength(5),

                        TextInput::make('toleransi_telat_menit')
                            ->label('Toleransi Telat (Menit)')
                            ->numeric()
                            ->default(10)
                            ->required(),
                    ]),

                    Grid::make(2)->schema([
                        Toggle::make('is_overnight')
                            ->label('Shift Lintas Tengah Malam (Misal 23:00 – 07:00)')
                            ->helperText('Aktifkan untuk Shift 3 / Shift Malam agar scan setelah 00:00 tidak salah terdeteksi.')
                            ->default(false),

                        Toggle::make('is_active')
                            ->label('Shift Aktif')
                            ->default(true),
                    ]),

                    TextInput::make('keterangan')
                        ->label('Keterangan')
                        ->maxLength(255)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode')->label('Kode')->badge()->color('primary')->searchable()->sortable(),
                TextColumn::make('nama')->label('Nama Shift')->weight('bold')->searchable(),
                TextColumn::make('jam_masuk')->label('Masuk')->alignCenter(),
                TextColumn::make('jam_pulang')->label('Pulang')->alignCenter(),
                TextColumn::make('toleransi_telat_menit')->label('Toleransi')->suffix(' mnt')->alignCenter(),
                IconColumn::make('is_overnight')->label('Lintas Malam')->boolean()->alignCenter(),
                TextColumn::make('karyawans_count')->label('Karyawan')->counts('karyawans')->badge()->color('success')->alignCenter(),
                IconColumn::make('is_active')->label('Aktif')->boolean()->alignCenter(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListHrShifts::route('/'),
            'create' => Pages\CreateHrShift::route('/create'),
            'edit'   => Pages\EditHrShift::route('/{record}/edit'),
        ];
    }
}
