<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AbsensiResource\Pages;
use App\Models\Absensi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AbsensiResource extends Resource
{
    protected static ?string $model = Absensi::class;
    protected static ?string $navigationGroup = 'HRD';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-finger-print';

    public static function getNavigationLabel(): string
    {
        return 'Absensi & Rekap';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Data Karyawan')
                ->schema([
                    Forms\Components\Select::make('karyawan_id')
                        ->label('Karyawan')
                        ->relationship('karyawan', 'nama')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $karyawan = \App\Models\Karyawan::with('departemen')->find($state);
                            if (! $karyawan) {
                                return;
                            }
                            $set('nip', $karyawan->nip);
                            $set('pin', $karyawan->pin_finger ?: $karyawan->nip);
                            $set('jabatan', $karyawan->jabatan);
                            $set('departemen', $karyawan->departemen?->nama_departemen);
                            $set('kantor', $karyawan->kantor);
                        })
                        ->required(),

                    Forms\Components\TextInput::make('nip')->disabled()->dehydrated(),
                    Forms\Components\TextInput::make('pin')->disabled()->dehydrated(),
                    Forms\Components\TextInput::make('jabatan')->disabled()->dehydrated(),
                    Forms\Components\TextInput::make('departemen')
                        ->disabled()
                        ->dehydrated()
                        ->afterStateHydrated(function ($state, callable $set, $record) {
                            if (! $record || ! $record->karyawan) {
                                return;
                            }
                            $set('departemen', $record->karyawan->departemen?->nama_departemen);
                        }),
                    Forms\Components\TextInput::make('kantor')->disabled()->dehydrated(),
                ])->columns(2),

            Forms\Components\Section::make('Data Scan Absensi')
                ->schema([
                    Forms\Components\DateTimePicker::make('tanggal_scan')
                        ->label('Tanggal & Jam Scan')
                        ->default(now())
                        ->required(),

                    Forms\Components\Select::make('io')
                        ->label('IO (Masuk / Keluar)')
                        ->options([
                            'Masuk'  => 'Masuk',
                            'Keluar' => 'Keluar',
                        ])
                        ->default('Masuk')
                        ->required(),

                    Forms\Components\TextInput::make('mesin')->label('Mesin')->default('Manual'),
                    Forms\Components\TextInput::make('sn')->label('SN Mesin'),
                    Forms\Components\TextInput::make('workcode')->label('Workcode'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('nip')->searchable(),
                Tables\Columns\TextColumn::make('departemen')->searchable(),
                Tables\Columns\TextColumn::make('tanggal_scan')
                    ->label('Tanggal & Jam Scan')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('io')
                    ->label('IO')
                    ->badge()
                    ->color(fn (?string $state) => $state === 'Masuk' ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('mesin')->label('Sumber/Mesin')->toggleable(),
            ])
            ->defaultSort('tanggal_scan', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('karyawan_id')
                    ->label('Karyawan')
                    ->relationship('karyawan', 'nama')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('io')
                    ->label('IO')
                    ->options(['Masuk' => 'Masuk', 'Keluar' => 'Keluar']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAbsensis::route('/'),
            'create' => Pages\CreateAbsensi::route('/create'),
            'edit'   => Pages\EditAbsensi::route('/{record}/edit'),
        ];
    }
}
