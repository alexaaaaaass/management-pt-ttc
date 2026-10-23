<?php

namespace App\Filament\Admin\Resources\MasterCOAResource\Pages;

use App\Filament\Admin\Resources\MasterCOAResource;
use App\Models\MasterCOA;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class ImportMasterCOA extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static string $resource = MasterCOAResource::class;

    protected static string $view =
        'filament.admin.resources.master-coa-resource.pages.import-master-coa';

    protected static ?string $title = 'Import Excel COA';

    public ?array $data = [];

    public array $previewRows = [];

    public bool $hasPreview = false;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('file')
                    ->label('File Excel COA')
                    ->disk('local')
                    ->directory('imports/coa')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                    ])
                    ->maxSize(10240)
                    ->required(),
            ])
            ->statePath('data');
    }

    public function preview(): void
    {
        $this->hasPreview = false;
        $this->previewRows = [];

        try {
            $state = $this->form->getState();
            $file = $state['file'] ?? null;

            if (! is_string($file) || $file === '') {
                Notification::make()
                    ->title('Pilih file Excel terlebih dahulu')
                    ->warning()
                    ->send();

                return;
            }

            $path = Storage::disk('local')->path($file);

            if (! is_file($path)) {
                Notification::make()
                    ->title('File Excel tidak ditemukan')
                    ->danger()
                    ->send();

                return;
            }

            $spreadsheet = IOFactory::load($path);

            try {
                $sheet = $spreadsheet->getActiveSheet()->toArray(
                    null,
                    true,
                    true,
                    false
                );
            } finally {
                $spreadsheet->disconnectWorksheets();
            }

            unset($spreadsheet);

            if (count($sheet) < 2) {
                Notification::make()
                    ->title('File Excel kosong atau tidak memiliki data')
                    ->danger()
                    ->send();

                return;
            }

            // Normalisasi header agar tidak sensitif huruf besar/kecil
            // dan spasi di awal atau akhir nama kolom.
            $headers = array_map(
                fn ($value) => strtolower(trim((string) $value)),
                $sheet[0]
            );

            $requiredHeaders = [
                'code',
                'name',
                'parent code',
                'level',
                'account type',
                'account class',
                'financial statement',
                'cash flow type',
                'posting',
                'normal balance',
            ];

            $headerIndexes = [];

            foreach ($requiredHeaders as $header) {
                $index = array_search($header, $headers, true);

                if ($index === false) {
                    Notification::make()
                        ->title('Header Excel tidak lengkap')
                        ->body(
                            "Kolom '{$header}' tidak ditemukan. " .
                            'Gunakan format COA.xlsx dengan 10 kolom yang diperlukan.'
                        )
                        ->danger()
                        ->send();

                    return;
                }

                $headerIndexes[$header] = $index;
            }

            $existingCodes = MasterCOA::query()
                ->pluck('kode_akun')
                ->map(fn ($code) => strtoupper(trim((string) $code)))
                ->all();

            $existingCodes = array_fill_keys($existingCodes, true);

            $seenCodes = [];
            $this->previewRows = [];

            foreach (array_slice($sheet, 1, null, true) as $index => $row) {
                $code = trim((string) ($row[$headerIndexes['code']] ?? ''));
                $name = trim((string) ($row[$headerIndexes['name']] ?? ''));

                if ($code === '' && $name === '') {
                    continue;
                }

                $key = strtoupper($code);

                $levelValue = trim(
                    (string) ($row[$headerIndexes['level']] ?? '')
                );

                $validLevel = $levelValue === ''
                    || (ctype_digit($levelValue) && (int) $levelValue >= 0);

                if ($code === '' || $name === '' || ! $validLevel) {
                    $status = 'Tidak valid';
                } elseif (isset($seenCodes[$key])) {
                    $status = 'Duplikat dalam Excel';
                } elseif (isset($existingCodes[$key])) {
                    $status = 'Sudah ada';
                } else {
                    $status = 'Baru';
                }

                if ($code !== '') {
                    $seenCodes[$key] = true;
                }

                $this->previewRows[] = [
                    'row' => $index + 1,
                    'code' => $code,
                    'name' => $name,
                    'parent_code' => trim(
                        (string) ($row[$headerIndexes['parent code']] ?? '')
                    ),
                    'level' => $levelValue,
                    'account_type' => trim(
                        (string) ($row[$headerIndexes['account type']] ?? '')
                    ),
                    'account_class' => trim(
                        (string) ($row[$headerIndexes['account class']] ?? '')
                    ),
                    'financial_statement' => trim(
                        (string) ($row[$headerIndexes['financial statement']] ?? '')
                    ),
                    'cash_flow_type' => trim(
                        (string) ($row[$headerIndexes['cash flow type']] ?? '')
                    ),
                    'posting' => trim(
                        (string) ($row[$headerIndexes['posting']] ?? '')
                    ),
                    'normal_balance' => trim(
                        (string) ($row[$headerIndexes['normal balance']] ?? '')
                    ),
                    'status' => $status,
                ];
            }

            if (empty($this->previewRows)) {
                Notification::make()
                    ->title('Tidak ada baris COA untuk dipratinjau')
                    ->warning()
                    ->send();

                return;
            }

            $this->hasPreview = true;

            Notification::make()
                ->title('Pratinjau COA selesai')
                ->body(
                    'Periksa status setiap akun sebelum menjalankan import.'
                )
                ->success()
                ->send();
        } catch (Throwable $e) {
            report($e);

            Notification::make()
                ->title('Gagal membaca file Excel')
                ->body(
                    'Periksa format file dan pastikan file Excel dapat dibuka.'
                )
                ->danger()
                ->send();
        }
    }

    public function import(): void
    {
        if (! $this->hasPreview || empty($this->previewRows)) {
            Notification::make()
                ->title('Lakukan pratinjau terlebih dahulu')
                ->warning()
                ->send();

            return;
        }

        $created = 0;
        $skipped = 0;

        try {
            DB::transaction(function () use (&$created, &$skipped) {
                $existingCodes = MasterCOA::query()
                    ->pluck('kode_akun')
                    ->map(fn ($code) => strtoupper(trim((string) $code)))
                    ->all();

                $existingCodes = array_fill_keys($existingCodes, true);

                foreach ($this->previewRows as $row) {
                    $code = trim((string) ($row['code'] ?? ''));
                    $name = trim((string) ($row['name'] ?? ''));
                    $key = strtoupper($code);

                    // Hanya impor baris berstatus Baru.
                    // Data lama, duplikat, dan baris tidak valid dilewati.
                    if (
                        ($row['status'] ?? '') !== 'Baru'
                        || $code === ''
                        || $name === ''
                        || isset($existingCodes[$key])
                    ) {
                        $skipped++;
                        continue;
                    }

                    $levelValue = trim((string) ($row['level'] ?? ''));

                    MasterCOA::create([
                        // Nilai bawaan tetap mengikuti importer sebelumnya.
                        'karyawan_id' => 1,
                        'coa_class_id' => 1,
                        'periode' => '2026-04',
                        'gudang' => 'TTC',

                        'kode_akun' => $code,
                        'nama_akun' => $name,

                        'parent_code' => $row['parent_code'] !== ''
                            ? $row['parent_code']
                            : null,

                        'level' => $levelValue !== ''
                            ? (int) $levelValue
                            : null,

                        'account_type' => $row['account_type'] !== ''
                            ? $row['account_type']
                            : null,

                        'account_class' => $row['account_class'] !== ''
                            ? $row['account_class']
                            : null,

                        'financial_statement' => $row['financial_statement'] !== ''
                            ? $row['financial_statement']
                            : null,

                        'cash_flow_type' => $row['cash_flow_type'] !== ''
                            ? $row['cash_flow_type']
                            : null,

                        'posting' => $row['posting'] !== ''
                            ? $row['posting']
                            : null,

                        'normal_balance' => $row['normal_balance'] !== ''
                            ? $row['normal_balance']
                            : null,

                        'saldo_debit' => 0,
                        'saldo_kredit' => 0,
                        'nominal_default' => 0,
                        'keterangan' => 'Import Excel COA',
                        'status' => 'aktif',
                    ]);

                    $existingCodes[$key] = true;
                    $created++;
                }
            });

            $this->hasPreview = false;
            $this->previewRows = [];

            Notification::make()
                ->title('Import COA selesai')
                ->body(
                    "Akun baru: {$created}. Dilewati: {$skipped}."
                )
                ->success()
                ->send();
        } catch (Throwable $e) {
            report($e);

            Notification::make()
                ->title('Import COA gagal')
                ->body(
                    'Transaksi dibatalkan. Periksa log Laravel untuk detail kesalahan.'
                )
                ->danger()
                ->send();
        }
    }
}
