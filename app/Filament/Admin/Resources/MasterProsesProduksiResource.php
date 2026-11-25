<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MasterProsesProduksiResource\Pages;
use App\Models\MasterProsesProduksi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MasterProsesProduksiResource extends Resource
{
    protected static ?string $model = MasterProsesProduksi::class;

    protected static ?string $navigationGroup = 'Ppic';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationLabel = 'Master Proses Produksi';

    protected static ?string $modelLabel = 'Master Proses Produksi';

    protected static ?string $pluralModelLabel = 'Master Proses Produksi';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Detail Master Proses Produksi')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('stasiun')
                        ->label('Stasiun Produksi')
                        ->options(MasterProsesProduksi::stasiunOptions())
                        ->default(MasterProsesProduksi::STASIUN_PRINTING)
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set, string $operation) {
                            if ($operation === 'create' && filled($state)) {
                                $set('kode_proses', MasterProsesProduksi::suggestKode((string) $state));
                            }
                        }),

                    Forms\Components\TextInput::make('kode_proses')
                        ->label('Kode Proses')
                        ->default(fn () => MasterProsesProduksi::suggestKode(MasterProsesProduksi::STASIUN_PRINTING))
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50),

                    Forms\Components\TextInput::make('nama_proses')
                        ->label('Nama Proses')
                        ->placeholder('Contoh: Emboss Inline (Flexo) / Pond / Protol')
                        ->required()
                        ->maxLength(120),

                    Forms\Components\TextInput::make('urutan')
                        ->label('Urutan Tampil')
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->required(),

                    Forms\Components\TextInput::make('keterangan')
                        ->label('Keterangan (Opsional)')
                        ->placeholder('Penjelasan singkat cakupan proses')
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Status Aktif')
                        ->default(true)
                        ->helperText('Nonaktifkan jika proses tidak lagi digunakan di form laporan produksi'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('kode_proses')
                    ->label('Kode')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('stasiun')
                    ->label('Stasiun')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => MasterProsesProduksi::stasiunOptions()[$state] ?? (string) $state)
                    ->color(fn (?string $state): string => match ($state) {
                        MasterProsesProduksi::STASIUN_PRINTING  => 'primary',
                        MasterProsesProduksi::STASIUN_DIEMAKING => 'info',
                        MasterProsesProduksi::STASIUN_FINISHING => 'warning',
                        default                                 => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_proses')
                    ->label('Nama Proses')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('urutan')
                    ->label('Urutan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(45)
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stasiun')
                    ->label('Stasiun Produksi')
                    ->options(MasterProsesProduksi::stasiunOptions()),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
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
            'index'  => Pages\ListMasterProsesProduksis::route('/'),
            'create' => Pages\CreateMasterProsesProduksi::route('/create'),
            'edit'   => Pages\EditMasterProsesProduksi::route('/{record}/edit'),
        ];
    }
}
