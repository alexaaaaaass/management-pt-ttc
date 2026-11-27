<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\NpbApprovalResource\Pages;
use App\Models\MasterCOA;
use App\Models\NpbRequest;
use App\Services\Finance\NpbService;
use App\Support\MoneyHelper;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class NpbApprovalResource extends Resource
{
    protected static ?string $model = NpbRequest::class;
    protected static ?string $slug = 'npb-approvals';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 3;
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $navigationLabel = 'Approval NPB';
    protected static ?string $modelLabel = 'Approval NPB';
    protected static ?string $pluralModelLabel = 'Approval NPB';

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

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = NpbRequest::whereIn('status', ['DRAFT', 'SUBMITTED', 'REVIEWED'])->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_pengajuan')
                    ->label('No. Pengajuan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('tanggal_pengajuan')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('periode_utama')
                    ->label('Periode')
                    ->description(fn (NpbRequest $r) => $r->periode_proyeksi ? 'Proyeksi: ' . $r->periode_proyeksi : '1 Bulan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_kebutuhan_utama')
                    ->label('Kebutuhan Utama')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignRight()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('kekurangan_pendanaan')
                    ->label('Kurang / Surplus')
                    ->formatStateUsing(fn ($state) => ((float) $state < 0 ? '-' : '') . 'Rp ' . number_format(abs((float) $state), 0, ',', '.'))
                    ->color(fn ($state) => (float) $state < 0 ? 'danger' : 'success')
                    ->weight('bold')
                    ->alignRight(),

                Tables\Columns\ToggleColumn::make('is_approved_status')
                    ->label('Approved (Owner)')
                    ->getStateUsing(fn (NpbRequest $record): bool => $record->is_approved_status)
                    ->onColor('success')
                    ->offColor('gray')
                    ->onIcon('heroicon-m-check')
                    ->offIcon('heroicon-m-x-mark')
                    ->disabled(fn (NpbRequest $record): bool => in_array($record->status, ['DISBURSED', 'PARTIAL_SETTLED', 'SETTLED'], true))
                    ->tooltip(fn (NpbRequest $record): string => in_array($record->status, ['DISBURSED', 'PARTIAL_SETTLED', 'SETTLED'], true)
                        ? 'Terkunci — NPB sudah dicairkan / masuk LPJ'
                        : ($record->is_approved_status
                            ? 'Aktif (APPROVED) — klik untuk menonaktifkan'
                            : 'Belum approved — aktifkan jika Owner sudah menyetujui'))
                    ->updateStateUsing(function (NpbRequest $record, $state): bool {
                        $approved = (bool) $state;

                        if ($approved) {
                            $record->update([
                                'status'         => 'APPROVED',
                                'disetujui_oleh' => $record->disetujui_oleh ?: 'Owner',
                                'disetujui_at'   => $record->disetujui_at ?: now(),
                            ]);

                            Notification::make()
                                ->title("NPB {$record->nomor_pengajuan} diaktifkan (APPROVED)")
                                ->body('Stempel merah APPROVED otomatis aktif di PDF NPB.')
                                ->success()
                                ->send();

                            return true;
                        }

                        if (in_array($record->status, ['DISBURSED', 'PARTIAL_SETTLED', 'SETTLED'], true)) {
                            Notification::make()
                                ->title('Approval terkunci karena NPB sudah dicairkan / masuk LPJ')
                                ->warning()
                                ->send();

                            return true;
                        }

                        $record->update([
                            'status'       => 'SUBMITTED',
                            'disetujui_at' => null,
                        ]);

                        Notification::make()
                            ->title("Approval NPB {$record->nomor_pengajuan} dinonaktifkan")
                            ->body('Status kembali ke Diajukan dan stempel APPROVED di PDF disembunyikan.')
                            ->warning()
                            ->send();

                        return false;
                    }),

                Tables\Columns\TextColumn::make('disetujui_at')
                    ->label('Tgl Approved')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => NpbRequest::STATUS_OPTIONS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'DRAFT'           => 'gray',
                        'SUBMITTED'       => 'info',
                        'REVIEWED'        => 'warning',
                        'APPROVED'        => 'success',
                        'DISBURSED'       => 'primary',
                        'PARTIAL_SETTLED' => 'warning',
                        'SETTLED'         => 'success',
                        'REJECTED'        => 'danger',
                        default           => 'gray',
                    }),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(NpbRequest::STATUS_OPTIONS),
            ])
            ->actions([
                Action::make('previewPdf')
                    ->label('PDF')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn (NpbRequest $record) => route('finance.npb.preview', $record))
                    ->openUrlInNewTab(),

                ActionGroup::make([
                    Action::make('approveWithNote')
                        ->label('Approve & Atur Nominal ACC / Pencairan')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->visible(fn (NpbRequest $r) => ! in_array($r->status, ['PARTIAL_SETTLED', 'SETTLED'], true))
                        ->form([
                            TextInput::make('disetujui_oleh')
                                ->label('Nama / Jabatan Penyetuju (Owner)')
                                ->default(fn (NpbRequest $record) => $record->disetujui_oleh ?: 'Owner')
                                ->required(),
                            DateTimePicker::make('disetujui_at')
                                ->label('Tanggal Approved')
                                ->default(fn (NpbRequest $record) => $record->disetujui_at ?: now())
                                ->required(),
                            Textarea::make('catatan_approval')
                                ->label('Catatan Persetujuan (Opsional)')
                                ->default(fn (NpbRequest $record) => $record->catatan_approval)
                                ->rows(2),
                            Toggle::make('langsung_cairkan')
                                ->label('Sekaligus Catat Nominal Disetujui / Dicairkan')
                                ->helperText('Aktifkan bila Owner menyetujui nominal parsial (misal pengajuan Rp 100 jt, di-ACC Rp 80 jt). Pencatatan uang masuk ke COA Bank tetap manual via Bank Masuk (tidak otomatis mengisi COA Bank).')
                                ->default(false)
                                ->live(),
                            DatePicker::make('tanggal_pencairan')
                                ->label('Tanggal Pencairan')
                                ->default(fn (NpbRequest $r) => $r->tanggal_pencairan ?: now())
                                ->visible(fn (Get $get) => (bool) $get('langsung_cairkan'))
                                ->required(fn (Get $get) => (bool) $get('langsung_cairkan')),
                            TextInput::make('nominal_dicairkan')
                                ->label('Nominal Disetujui & Dicairkan (Rp)')
                                ->prefix('Rp')
                                ->currencyMask(2)
                                ->default(fn (NpbRequest $r) => $r->nominal_dicairkan > 0 ? $r->nominal_dicairkan : (abs(min(0, (float) $r->kekurangan_pendanaan)) ?: (float) $r->total_kebutuhan_utama))
                                ->visible(fn (Get $get) => (bool) $get('langsung_cairkan'))
                                ->required(fn (Get $get) => (bool) $get('langsung_cairkan')),
                            Select::make('sumber_dana_pencairan')
                                ->label('Sumber Dana Pencairan')
                                ->options(NpbRequest::SUMBER_DANA_OPTIONS)
                                ->default(fn (NpbRequest $r) => $r->sumber_dana_pencairan ?: 'MODAL_PEMILIK')
                                ->visible(fn (Get $get) => (bool) $get('langsung_cairkan'))
                                ->required(fn (Get $get) => (bool) $get('langsung_cairkan')),
                            Select::make('coa_tujuan_pencairan_id')
                                ->label('Rekening Bank / Kas Tujuan (Opsional)')
                                ->placeholder('Pilih manual jika perlu (tidak otomatis memposting ke COA Bank)')
                                ->options(fn () => MasterCOA::where(function ($q) {
                                    $q->where('kode_akun', 'like', '111%')
                                        ->orWhere('account_class', 'like', '%Bank%')
                                        ->orWhere('account_class', 'like', '%Cash%');
                                })->orderBy('kode_akun')->get()->mapWithKeys(fn ($c) => [$c->id => trim((string) $c->kode_akun) . ' - ' . $c->nama_akun])->toArray())
                                ->default(fn (NpbRequest $r) => $r->coa_tujuan_pencairan_id)
                                ->searchable()
                                ->nullable()
                                ->visible(fn (Get $get) => (bool) $get('langsung_cairkan'))
                                ->required(fn (Get $get) => (bool) $get('langsung_cairkan') && (bool) $get('post_to_ledger')),
                            Toggle::make('post_to_ledger')
                                ->label('Auto-Posting Jurnal Pencairan ke Buku Besar (Opsional)')
                                ->helperText('Default MATI (disarankan): input dana masuk tetap dilakukan manual melalui menu Bank Masuk agar tidak dobel.')
                                ->default(false)
                                ->live()
                                ->visible(fn (Get $get) => (bool) $get('langsung_cairkan')),
                        ])
                        ->action(function (NpbRequest $record, array $data) {
                            $update = [
                                'status'           => ! empty($data['langsung_cairkan']) ? 'DISBURSED' : 'APPROVED',
                                'disetujui_oleh'   => $data['disetujui_oleh'],
                                'disetujui_at'     => $data['disetujui_at'],
                                'catatan_approval' => $data['catatan_approval'] ?? null,
                            ];

                            $posted = false;
                            $nominal = 0.0;
                            if (! empty($data['langsung_cairkan'])) {
                                $nominal = round((float) (MoneyHelper::clean($data['nominal_dicairkan'] ?? 0) ?? 0), 2);
                                $update['tanggal_pencairan'] = $data['tanggal_pencairan'];
                                $update['nominal_dicairkan'] = $nominal;
                                $update['sumber_dana_pencairan'] = $data['sumber_dana_pencairan'];
                                $update['coa_tujuan_pencairan_id'] = $data['coa_tujuan_pencairan_id'] ?? null;
                            }

                            $record->update($update);

                            if (! empty($data['langsung_cairkan'])) {
                                $svc = app(NpbService::class);
                                if (! empty($data['post_to_ledger']) && $data['sumber_dana_pencairan'] !== 'DANA_INTERNAL') {
                                    $posted = $svc->postDisbursementToLedger($record->refresh());
                                } else {
                                    $svc->deleteDisbursementFromLedger($record->refresh());
                                }
                            }

                            Notification::make()
                                ->title("NPB {$record->nomor_pengajuan} " . (! empty($data['langsung_cairkan']) ? 'APPROVED & DICAIRKAN!' : 'APPROVED!'))
                                ->body(! empty($data['langsung_cairkan'])
                                    ? 'Dicairkan Rp ' . number_format($nominal, 0, ',', '.') . ($posted ? ' & diposting ke Buku Besar.' : ' (tidak mengubah COA Bank — silakan input manual di Bank Masuk).')
                                    : 'Detail approval tersimpan & stempel APPROVED aktif di PDF.')
                                ->success()
                                ->duration(8000)
                                ->send();
                        }),

                    Action::make('reject')
                        ->label('Tolak NPB (Reject)')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (NpbRequest $r) => ! in_array($r->status, ['DISBURSED', 'PARTIAL_SETTLED', 'SETTLED', 'REJECTED'], true))
                        ->form([
                            Textarea::make('catatan_approval')
                                ->label('Alasan Penolakan')
                                ->rows(2)
                                ->required(),
                        ])
                        ->action(function (NpbRequest $record, array $data) {
                            $record->update([
                                'status'           => 'REJECTED',
                                'disetujui_at'     => null,
                                'catatan_approval' => $data['catatan_approval'],
                            ]);

                            Notification::make()
                                ->title("NPB {$record->nomor_pengajuan} ditolak")
                                ->danger()
                                ->send();
                        }),

                    Action::make('downloadPdf')
                        ->label('Download PDF NPB')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->url(fn (NpbRequest $record) => route('finance.npb.download', $record))
                        ->openUrlInNewTab(),
                ]),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNpbApprovals::route('/'),
        ];
    }
}
