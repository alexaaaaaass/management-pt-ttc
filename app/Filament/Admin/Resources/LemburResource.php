<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LemburResource\Pages;
use App\Models\Lembur;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LemburResource extends Resource
{
    protected static ?string $model = Lembur::class;
    protected static ?string $navigationGroup = 'HRD';
    protected static ?int $navigationSort = 4;
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationIcon = 'heroicon-o-clock';

    public static function getNavigationLabel(): string
    {
        return 'Lembur';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Data Lembur Karyawan')
                ->schema([
                    Forms\Components\TextInput::make('kode_lembur')
                        ->label('Kode Lembur')
                        ->default('AUTO')
                        ->disabled()
                        ->dehydrated(),

                    Forms\Components\Select::make('karyawan_id')
                        ->label('Karyawan')
                        ->relationship('karyawan', 'nama')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\TextInput::make('kode_gudang')
                        ->label('Kode Gudang / Lokasi')
                        ->default('TTC')
                        ->required(),

                    Forms\Components\DatePicker::make('tanggal_lembur')
                        ->label('Tanggal Lembur')
                        ->default(now())
                        ->required(),

                    Forms\Components\TimePicker::make('jam_mulai')
                        ->label('Jam Mulai')
                        ->seconds(false)
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::hitungDurasi($get, $set)),

                    Forms\Components\TimePicker::make('jam_selesai')
                        ->label('Jam Selesai')
                        ->seconds(false)
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::hitungDurasi($get, $set)),

                    Forms\Components\TextInput::make('durasi_lembur')
                        ->label('Durasi (Jam)')
                        ->helperText('Otomatis dihitung (mendukung lembur melewati tengah malam)')
                        ->numeric()
                        ->minValue(0.1)
                        ->disabled()
                        ->dehydrated()
                        ->afterStateHydrated(fn ($state, callable $set, callable $get) => self::hitungDurasi($get, $set)),

                    Forms\Components\Select::make('status')
                        ->label('Status Approval')
                        ->options([
                            'Pending'  => 'Pending (Menunggu Approval)',
                            'Approved' => 'Approved',
                            'Rejected' => 'Rejected',
                        ])
                        ->default('Pending')
                        ->disabled()
                        ->dehydrated(),

                    Forms\Components\Textarea::make('keterangan')
                        ->label('Keterangan Pekerjaan Lembur')
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function hitungDurasi(callable $get, callable $set): void
    {
        $mulai = $get('jam_mulai');
        $selesai = $get('jam_selesai');

        if (! $mulai || ! $selesai) {
            return;
        }

        $start = Carbon::parse($mulai);
        $end = Carbon::parse($selesai);

        // Handle lembur melewati tengah malam
        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        $durasi = $start->diffInMinutes($end) / 60;
        $set('durasi_lembur', round($durasi, 2));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_lembur')
                    ->label('Kode')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kode_gudang')->label('Gudang'),

                Tables\Columns\TextColumn::make('tanggal_lembur')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jam_mulai')->label('Mulai'),
                Tables\Columns\TextColumn::make('jam_selesai')->label('Selesai'),

                Tables\Columns\TextColumn::make('durasi_lembur')
                    ->label('Durasi')
                    ->suffix(' Jam')
                    ->alignRight()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Approved' => 'success',
                        'Rejected' => 'danger',
                        default    => 'warning',
                    }),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('karyawan_id')
                    ->label('Karyawan')
                    ->relationship('karyawan', 'nama')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->options(['Pending' => 'Pending', 'Approved' => 'Approved', 'Rejected' => 'Rejected']),
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
            'index'  => Pages\ListLemburs::route('/'),
            'create' => Pages\CreateLembur::route('/create'),
            'edit'   => Pages\EditLembur::route('/{record}/edit'),
        ];
    }
}
