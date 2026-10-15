<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CutiResource\Pages;
use App\Models\Cuti;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Carbon\Carbon;

class CutiResource extends Resource
{
    protected static ?string $model = Cuti::class;
    protected static ?string $navigationGroup = 'HRD';
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?int $navigationSort = 7;

    public static function getNavigationLabel(): string
    {
        return 'Cuti';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Pengajuan Cuti')
                ->schema([
                    Forms\Components\Select::make('karyawan_id')
                        ->label('Karyawan')
                        ->relationship('karyawan', 'nama')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('jenis_cuti')
                        ->label('Jenis Cuti')
                        ->options([
                            'Cuti Tahunan'   => 'Cuti Tahunan',
                            'Cuti Menikah'   => 'Cuti Menikah',
                            'Cuti Melahirkan'=> 'Cuti Melahirkan',
                            'Cuti Sakit'     => 'Cuti Sakit',
                            'Cuti Khusus'    => 'Cuti Khusus',
                        ])
                        ->searchable()
                        ->required(),

                    Forms\Components\DatePicker::make('tanggal_mulai')
                        ->label('Tanggal Mulai')
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::hitungHariCuti($get, $set)),

                    Forms\Components\DatePicker::make('tanggal_selesai')
                        ->label('Tanggal Selesai')
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::hitungHariCuti($get, $set)),

                    Forms\Components\TextInput::make('jumlah_hari')
                        ->label('Jumlah Hari')
                        ->disabled()
                        ->dehydrated()
                        ->numeric()
                        ->default(0),

                    Forms\Components\FileUpload::make('lampiran')
                        ->directory('cuti')
                        ->image()
                        ->downloadable()
                        ->openable(),

                    Forms\Components\Textarea::make('keterangan')
                        ->label('Keterangan Cuti')
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function hitungHariCuti(callable $get, callable $set): void
    {
        $mulai = $get('tanggal_mulai');
        $selesai = $get('tanggal_selesai');

        if (!$mulai || !$selesai) {
            return;
        }

        $startDate = Carbon::parse($mulai);
        $endDate = Carbon::parse($selesai);

        if ($endDate->lessThan($startDate)) {
            $set('jumlah_hari', 0);
            return;
        }

        $set('jumlah_hari', $startDate->diffInDays($endDate) + 1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('jenis_cuti')
                    ->label('Jenis Cuti')
                    ->colors([
                        'primary' => 'Cuti Tahunan',
                        'success' => 'Cuti Menikah',
                        'info'    => 'Cuti Melahirkan',
                        'danger'  => 'Cuti Sakit',
                        'warning' => 'Cuti Khusus',
                    ]),

                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('Tgl Mulai')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->label('Tgl Selesai')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jumlah_hari')
                    ->label('Hari')
                    ->suffix(' Hari')
                    ->alignRight()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(40),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCutis::route('/'),
            'create' => Pages\CreateCuti::route('/create'),
            'edit'   => Pages\EditCuti::route('/{record}/edit'),
        ];
    }
}