<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ReportFinaceResource\Pages;
use App\Models\CoaMovement;
use App\Models\MasterCOA;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReportFinaceResource extends Resource
{
    protected static ?string $model = MasterCOA::class;

    protected static ?string $navigationGroup = 'Report';
    protected static ?string $navigationLabel = 'Trial Balance';
    protected static ?string $modelLabel = 'Trial Balance';
    protected static ?string $pluralModelLabel = 'Trial Balance';
    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('coaClass')
            ->orderBy('kode_akun');
    }

    private static function filterDates($livewire): array
    {
        $filters = $livewire->getTableFilterState('periode') ?? [];

        return [
            $filters['tanggal_mulai'] ?? null,
            $filters['tanggal_akhir'] ?? null,
        ];
    }

    private static function movementQuery(
        MasterCOA $record,
        $livewire,
        bool $beforeStart = false
    ) {
        $query = CoaMovement::query()
            ->where('coa_id', $record->id);

        [$start, $end] = self::filterDates($livewire);

        if ($start) {
            if ($beforeStart) {
                $query->whereDate('tanggal', '<', $start);
            } else {
                $query->whereDate('tanggal', '>=', $start);
            }
        }

        if ($end && ! $beforeStart) {
            $query->whereDate('tanggal', '<=', $end);
        }

        return $query;
    }

    private static function openingBalance(
        MasterCOA $record,
        $livewire
    ): float {
        [$start] = self::filterDates($livewire);

        $balance = (float) $record->saldo_debit
            - (float) $record->saldo_kredit;

        if ($start) {
            $beforeDebit = (float) self::movementQuery(
                $record,
                $livewire,
                true
            )->sum('debit');

            $beforeCredit = (float) self::movementQuery(
                $record,
                $livewire,
                true
            )->sum('kredit');

            $balance += $beforeDebit - $beforeCredit;
        }

        return $balance;
    }

    private static function periodDebit(
        MasterCOA $record,
        $livewire
    ): float {
        return (float) self::movementQuery(
            $record,
            $livewire
        )->sum('debit');
    }

    private static function periodCredit(
        MasterCOA $record,
        $livewire
    ): float {
        return (float) self::movementQuery(
            $record,
            $livewire
        )->sum('kredit');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_akun')
                    ->label('Kode Akun')
                    ->formatStateUsing(
                        fn ($state) => trim((string) $state)
                    )
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_akun')
                    ->label('Nama Akun')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('coaClass.name')
                    ->label('Klasifikasi'),

                Tables\Columns\TextColumn::make('saldo_awal_debit')
                    ->label('Saldo Awal Debit')
                    ->state(function (
                        MasterCOA $record,
                        $livewire
                    ): float {
                        return max(
                            0,
                            self::openingBalance($record, $livewire)
                        );
                    })
                    ->money('IDR')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('saldo_awal_kredit')
                    ->label('Saldo Awal Kredit')
                    ->state(function (
                        MasterCOA $record,
                        $livewire
                    ): float {
                        return max(
                            0,
                            -self::openingBalance($record, $livewire)
                        );
                    })
                    ->money('IDR')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('total_mutasi_debit')
                    ->label('Mutasi Debit')
                    ->state(fn (
                        MasterCOA $record,
                        $livewire
                    ): float => self::periodDebit($record, $livewire))
                    ->money('IDR')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('total_mutasi_kredit')
                    ->label('Mutasi Kredit')
                    ->state(fn (
                        MasterCOA $record,
                        $livewire
                    ): float => self::periodCredit($record, $livewire))
                    ->money('IDR')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('saldo_akhir_debit')
                    ->label('Saldo Akhir Debit')
                    ->state(function (
                        MasterCOA $record,
                        $livewire
                    ): float {
                        $ending = self::openingBalance($record, $livewire)
                            + self::periodDebit($record, $livewire)
                            - self::periodCredit($record, $livewire);

                        return max(0, $ending);
                    })
                    ->money('IDR')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('saldo_akhir_kredit')
                    ->label('Saldo Akhir Kredit')
                    ->state(function (
                        MasterCOA $record,
                        $livewire
                    ): float {
                        $ending = self::openingBalance($record, $livewire)
                            + self::periodDebit($record, $livewire)
                            - self::periodCredit($record, $livewire);

                        return max(0, -$ending);
                    })
                    ->money('IDR')
                    ->alignRight(),
            ])
            ->filters([
                Tables\Filters\Filter::make('periode')
                    ->label('Periode Laporan')
                    ->form([
                        Forms\Components\DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai')
                            ->native(false),

                        Forms\Components\DatePicker::make('tanggal_akhir')
                            ->label('Tanggal Akhir')
                            ->native(false),
                    ]),

                Tables\Filters\SelectFilter::make('coa_class_id')
                    ->label('Klasifikasi Akun')
                    ->relationship('coaClass', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Akun')
                    ->options([
                        'aktif' => 'Aktif',
                        'nonaktif' => 'Nonaktif',
                    ]),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReportFinaces::route('/'),
        ];
    }
}