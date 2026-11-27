<?php

namespace App\Filament\Admin\Resources\KaryawanResource\RelationManagers;

use App\Models\KaryawanDisiplin;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DisiplinsRelationManager extends RelationManager
{
    protected static string $relationship = 'disiplins';
    protected static ?string $title = 'Catatan Kedisiplinan (SP & Scorsing)';
    protected static ?string $icon = 'heroicon-o-exclamation-triangle';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('nomor_surat')
                ->label('Nomor Surat')
                ->default('AUTO')
                ->helperText('Otomatis: SP1/HRD/YYYYMM/001')
                ->maxLength(60),

            Select::make('jenis')
                ->label('Jenis Sanksi / Pembinaan')
                ->options(KaryawanDisiplin::JENIS_OPTIONS)
                ->default('SP1')
                ->live()
                ->required(),

            DatePicker::make('tanggal_surat')
                ->label('Tanggal Surat')
                ->default(now())
                ->required(),

            Select::make('status')
                ->label('Status')
                ->options(KaryawanDisiplin::STATUS_OPTIONS)
                ->default('Aktif')
                ->required(),

            DatePicker::make('tanggal_mulai')
                ->label('Mulai Berlaku / Mulai Scorsing'),

            DatePicker::make('tanggal_selesai')
                ->label('Berakhir / Selesai Scorsing'),

            TextInput::make('jumlah_hari_scorsing')
                ->label('Jumlah Hari Scorsing (Potong Gaji Harian)')
                ->numeric()
                ->default(0)
                ->visible(fn (callable $get) => $get('jenis') === 'SCORSING')
                ->helperText('Otomatis ikut dihitung sebagai potongan kehadiran di Payroll periode terkait.'),

            TextInput::make('diterbitkan_oleh')
                ->label('Diterbitkan Oleh')
                ->default(fn () => auth()->user()?->name ?? 'HRD PT TTC'),

            Textarea::make('pelanggaran')
                ->label('Uraian Pelanggaran / Alasan')
                ->required()
                ->rows(3)
                ->columnSpanFull(),

            Textarea::make('tindakan')
                ->label('Tindakan / Sanksi / Catatan Evaluasi')
                ->rows(2)
                ->columnSpanFull(),

            FileUpload::make('lampiran')
                ->label('Lampiran Surat SP / Scorsing (PDF / JPG / PNG)')
                ->disk('public')
                ->directory('karyawan-disiplin')
                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(5120)
                ->openable()
                ->downloadable()
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_surat')->label('No. Surat')->badge()->color('gray')->searchable(),
                TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => KaryawanDisiplin::JENIS_OPTIONS[$state] ?? $state)
                    ->color(fn (?string $state) => match ($state) {
                        'SCORSING', 'SP3' => 'danger',
                        'SP2', 'SP1'      => 'warning',
                        default           => 'info',
                    }),
                TextColumn::make('tanggal_surat')->label('Tanggal')->date('d/m/Y')->sortable(),
                TextColumn::make('jumlah_hari_scorsing')
                    ->label('Scorsing')
                    ->formatStateUsing(fn ($state) => (int) $state > 0 ? "{$state} Hari" : '-')
                    ->alignCenter(),
                TextColumn::make('pelanggaran')->label('Pelanggaran')->limit(40),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Aktif'   => 'danger',
                        'Selesai' => 'success',
                        default   => 'gray',
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('+ Catat SP / Scorsing'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
