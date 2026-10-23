<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CoaLedgerResource;
use App\Filament\Admin\Resources\MasterCOAResource\Pages;
use App\Models\MasterCOA;
use App\Services\Finance\TreasuryService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class MasterCOAResource extends Resource
{
    protected static ?string $model = MasterCOA::class;

    protected static ?string $navigationGroup = 'Finance';

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?int $navigationSort = 6;

    public static function getNavigationLabel(): string
    {
        return 'Master COA';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('karyawan_id')
                ->relationship('karyawan', 'nama')
                ->searchable()
                ->required(),

            Forms\Components\Select::make('coa_class_id')
                ->label('COA Class')
                ->relationship('coaClass', 'name')
                ->preload()
                ->required(),

            Forms\Components\TextInput::make('periode')
                ->placeholder('2026-04')
                ->required(),

            Forms\Components\TextInput::make('gudang')
                ->default('TTC'),

            Forms\Components\TextInput::make('kode_akun')
                ->label('Kode Akun')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('nama_akun')
                ->label('Nama Akun')
                ->required()
                ->maxLength(255),

            Forms\Components\Section::make('Detail COA')
                ->description('Informasi akun sesuai format Excel COA.')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('parent_code')
                        ->label('Parent Code')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('level')
                        ->label('Level')
                        ->numeric()
                        ->integer()
                        ->minValue(0),

                    Forms\Components\TextInput::make('account_type')
                        ->label('Account Type')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('account_class')
                        ->label('Account Class')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('financial_statement')
                        ->label('Financial Statement')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('cash_flow_type')
                        ->label('Cash Flow Type')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('posting')
                        ->label('Posting')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('normal_balance')
                        ->label('Normal Balance')
                        ->maxLength(255),
                ]),

            Forms\Components\Section::make('Saldo dan Pengaturan')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('saldo_debit')
                        ->label('Saldo Awal Debit')
                        ->numeric()
                        ->default(0),

                    Forms\Components\TextInput::make('saldo_kredit')
                        ->label('Saldo Awal Kredit')
                        ->numeric()
                        ->default(0),

                    Forms\Components\TextInput::make('nominal_default')
                        ->label('Nominal Default')
                        ->numeric()
                        ->default(0),

                    Forms\Components\Select::make('status')
                        ->options([
                            'aktif' => 'Aktif',
                            'nonaktif' => 'Nonaktif',
                        ])
                        ->default('aktif')
                        ->required(),

                    Forms\Components\Textarea::make('keterangan')
                        ->label('Keterangan')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_akun')
                    ->label('Kode')
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('nama_akun')
                    ->label('Nama Akun')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('parent_code')
                    ->label('Parent Code')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('level')
                    ->label('Level')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('account_type')
                    ->label('Account Type')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('account_class')
                    ->label('Account Class')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('financial_statement')
                    ->label('Financial Statement')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('cash_flow_type')
                    ->label('Cash Flow Type')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('posting')
                    ->label('Posting')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('normal_balance')
                    ->label('Normal Balance')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('coaClass.name')
                    ->label('COA Class')
                    ->badge(),

                Tables\Columns\TextColumn::make('saldo_debit')
                    ->label('Saldo Awal (D)')
                    ->money('IDR')
                    ->alignRight()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('saldo_kredit')
                    ->label('Saldo Awal (K)')
                    ->money('IDR')
                    ->alignRight()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('saldo_berjalan')
                    ->label('Saldo Berjalan')
                    ->money('IDR')
                    ->alignRight()
                    ->weight('bold')
                    ->color(
                        fn ($record) => $record->saldo_berjalan >= 0
                            ? 'success'
                            : 'danger'
                    ),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'aktif',
                        'danger' => 'nonaktif',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'aktif' => 'Aktif',
                        'nonaktif' => 'Nonaktif',
                    ]),

                Tables\Filters\SelectFilter::make('coa_class_id')
                    ->label('COA Class')
                    ->relationship('coaClass', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('ledger')
                    ->label('Buku Mutasi')
                    ->icon('heroicon-o-book-open')
                    ->color('info')
                    ->url(fn ($record) => CoaLedgerResource::getUrl('index', [
                        'tableFilters' => [
                            'coa_id' => ['value' => $record->id],
                        ],
                    ])),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->hidden(
                        fn ($record) => ! app(TreasuryService::class)
                            ->canDeleteCoa($record)
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('deleteSelected')
                        ->label('Hapus Akun Terpilih')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus akun COA terpilih?')
                        ->modalDescription(
                            'Akun yang tidak memenuhi aturan penghapusan akan dilewati.'
                        )
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $service = app(TreasuryService::class);
                            $deleted = 0;
                            $skipped = 0;

                            foreach ($records as $record) {
                                if (! $service->canDeleteCoa($record)) {
                                    $skipped++;
                                    continue;
                                }

                                $record->delete();
                                $deleted++;
                            }

                            Notification::make()
                                ->title('Proses hapus akun selesai')
                                ->body(
                                    "Berhasil dihapus: {$deleted}. Dilewati: {$skipped}."
                                )
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMasterCOAS::route('/'),
            'create' => Pages\CreateMasterCOA::route('/create'),
            'edit' => Pages\EditMasterCOA::route('/{record}/edit'),
            'import' => Pages\ImportMasterCOA::route('/import'),
        ];
    }
}
