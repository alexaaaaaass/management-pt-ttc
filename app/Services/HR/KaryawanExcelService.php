<?php

namespace App\Services\HR;

use App\Models\HrShift;
use App\Models\Karyawan;
use App\Models\MasterDepartemen;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class KaryawanExcelService
{
    public const COLUMNS = [
        'A' => 'NIP',
        'B' => 'PIN Finger',
        'C' => 'Nama Lengkap',
        'D' => 'Departemen',
        'E' => 'Shift',
        'F' => 'Jabatan',
        'G' => 'Skema Gaji',
        'H' => 'Status',
        'I' => 'Gaji Pokok',
        'J' => 'T. Jabatan',
        'K' => 'T. Kompetensi',
        'L' => 'T. Intensif',
        'M' => 'T. Makan',
        'N' => 'T. Hadir',
        'O' => 'Nama Bank',
        'P' => 'No Rekening',
        'Q' => 'Atas Nama Rek',
        'R' => 'No BPJS TK',
        'S' => 'Pot. BPJS TK',
        'T' => 'No BPJS Kes',
        'U' => 'Pot. BPJS Kes',
        'V' => 'NIK / KTP',
        'W' => 'No Telp / WA',
        'X' => 'Tgl Masuk (YYYY-MM-DD)',
        'Y' => 'Akhir Kontrak (YYYY-MM-DD)',
        'Z' => 'Masa Kontrak (1_BULAN/3_BULAN/6_BULAN/1_TAHUN/2_TAHUN/MANUAL/TETAP)',
    ];

    /**
     * Ekspor seluruh data Master Karyawan (atau template kosong) ke Excel (.xlsx).
     */
    public function exportSpreadsheet(bool $templateOnly = false): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Master Karyawan TTC');

        $sheet->setCellValue('A1', 'DATA MASTER KARYAWAN PT. TEKNOGRAFI TRI CAWANASKA');
        $sheet->setCellValue('A2', 'Petunjuk: Kolom NIP menjadi kunci pencocokan saat Impor (NIP yang sudah ada akan di-update, NIP baru akan didaftarkan).');
        $sheet->mergeCells('A1:Z1');
        $sheet->mergeCells('A2:Z2');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(9);

        foreach (self::COLUMNS as $col => $title) {
            $sheet->setCellValue($col . '4', $title);
        }

        $sheet->getStyle('A4:Z4')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(26);

        $rows = $templateOnly
            ? Karyawan::with(['departemen', 'shift'])->orderBy('nip')->limit(3)->get()
            : Karyawan::with(['departemen', 'shift'])->orderBy('nip')->get();

        $r = 5;
        foreach ($rows as $k) {
            $sheet->setCellValue('A' . $r, $k->nip);
            $sheet->setCellValue('B' . $r, $k->pin_finger);
            $sheet->setCellValue('C' . $r, $k->nama);
            $sheet->setCellValue('D' . $r, $k->departemen?->nama_departemen ?? 'Human Resource');
            $sheet->setCellValue('E' . $r, $k->shift?->kode ?? 'NON-SHIFT');
            $sheet->setCellValue('F' . $r, $k->jabatan);
            $sheet->setCellValue('G' . $r, $k->tipe_gaji ?: 'Bulanan');
            $sheet->setCellValue('H' . $r, $k->status_pegawai ?: 'Aktif');
            $sheet->setCellValue('I' . $r, (float) $k->gaji_pokok);
            $sheet->setCellValue('J' . $r, (float) $k->tunjangan_jabatan);
            $sheet->setCellValue('K' . $r, (float) $k->tunjangan_kompetensi);
            $sheet->setCellValue('L' . $r, (float) $k->tunjangan_intensif);
            $sheet->setCellValue('M' . $r, (float) $k->tunjangan_makan);
            $sheet->setCellValue('N' . $r, (float) $k->tunjangan_hadir);
            $sheet->setCellValue('O' . $r, $k->nama_bank);
            $sheet->setCellValueExplicit('P' . $r, (string) ($k->no_rekening ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('Q' . $r, $k->rekening_atas_nama);
            $sheet->setCellValueExplicit('R' . $r, (string) ($k->bpjs_ketenagakerjaan ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('S' . $r, (float) $k->bpjs_ketenagakerjaan_nominal);
            $sheet->setCellValueExplicit('T' . $r, (string) ($k->bpjs_kesehatan ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('U' . $r, (float) $k->bpjs_kesehatan_nominal);
            $sheet->setCellValueExplicit('V' . $r, (string) ($k->nik ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('W' . $r, (string) ($k->no_telp ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('X' . $r, $k->tanggal_masuk_kerja ? Carbon::parse($k->tanggal_masuk_kerja)->format('Y-m-d') : '');
            $sheet->setCellValue('Y' . $r, $k->tangal_akhir_kontrak ? Carbon::parse($k->tangal_akhir_kontrak)->format('Y-m-d') : '');
            $sheet->setCellValue('Z' . $r, $k->masa_kontrak ?? '');

            foreach (['I', 'J', 'K', 'L', 'M', 'N', 'S', 'U'] as $numCol) {
                $sheet->getStyle($numCol . $r)->getNumberFormat()->setFormatCode('#,##0');
            }
            $r++;
        }

        foreach (array_keys(self::COLUMNS) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    /**
     * Impor / Update Massal Master Karyawan dari Excel (gaji, PIN, rekening bank, BPJS, shift, kontrak).
     */
    public function importSpreadsheet(string $filePath): array
    {
        if (! is_file($filePath)) {
            throw new \InvalidArgumentException("File Excel karyawan tidak ditemukan: {$filePath}");
        }

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        // Cari baris header (mengandung "NIP" dan "Nama")
        $headerRow = 4;
        for ($r = 1; $r <= min(10, $highestRow); $r++) {
            $a = strtolower(trim((string) $sheet->getCell('A' . $r)->getValue()));
            $c = strtolower(trim((string) $sheet->getCell('C' . $r)->getValue()));
            if (str_contains($a, 'nip') && str_contains($c, 'nama')) {
                $headerRow = $r;
                break;
            }
        }

        $depts = MasterDepartemen::all();
        $defaultDeptId = $depts->first()?->id ?? 1;
        $shifts = HrShift::all();
        $defaultShiftId = $shifts->firstWhere('kode', 'NON-SHIFT')?->id ?? $shifts->first()?->id;

        $created = 0;
        $updated = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            for ($r = $headerRow + 1; $r <= $highestRow; $r++) {
                $nip = trim((string) $sheet->getCell('A' . $r)->getValue());
                $nama = trim((string) $sheet->getCell('C' . $r)->getValue());
                if ($nip === '' && $nama === '') {
                    continue;
                }
                if ($nama === '') {
                    $skipped++;
                    continue;
                }

                $deptText = trim((string) $sheet->getCell('D' . $r)->getValue());
                $dept = $deptText !== ''
                    ? $depts->first(fn ($d) => strcasecmp($d->nama_departemen, $deptText) === 0 || strcasecmp($d->kode, $deptText) === 0)
                    : null;

                $shiftText = trim((string) $sheet->getCell('E' . $r)->getValue());
                $shift = $shiftText !== ''
                    ? $shifts->first(fn ($s) => strcasecmp($s->kode, $shiftText) === 0 || strcasecmp($s->nama, $shiftText) === 0)
                    : null;

                $tipeGaji = trim((string) $sheet->getCell('G' . $r)->getValue()) ?: 'Bulanan';
                if (! in_array($tipeGaji, ['Bulanan', 'Harian', 'Freelance', 'Borongan'], true)) {
                    $tipeGaji = 'Bulanan';
                }

                $statusPegawai = trim((string) $sheet->getCell('H' . $r)->getValue()) ?: 'Aktif';
                if (! in_array($statusPegawai, ['Aktif', 'Nonaktif'], true)) {
                    $statusPegawai = 'Aktif';
                }

                $parseDate = function ($raw): ?string {
                    if ($raw === null || $raw === '') {
                        return null;
                    }
                    if (is_numeric($raw)) {
                        return ExcelDate::excelToDateTimeObject((float) $raw)->format('Y-m-d');
                    }
                    try {
                        return Carbon::parse((string) $raw)->format('Y-m-d');
                    } catch (\Throwable) {
                        return null;
                    }
                };

                $payload = [
                    'nip'                          => $nip ?: ('K' . str_pad((string) (Karyawan::count() + 1), 2, '0', STR_PAD_LEFT)),
                    'pin_finger'                   => trim((string) $sheet->getCell('B' . $r)->getValue()) ?: null,
                    'nama'                         => $nama,
                    'departemen_id'                => $dept?->id ?? $defaultDeptId,
                    'shift_id'                     => $shift?->id ?? $defaultShiftId,
                    'jabatan'                      => trim((string) $sheet->getCell('F' . $r)->getValue()) ?: 'Staff Umum',
                    'tipe_gaji'                    => $tipeGaji,
                    'is_borongan'                  => $tipeGaji === 'Borongan',
                    'status_pegawai'               => $statusPegawai,
                    'gaji_pokok'                   => round((float) $sheet->getCell('I' . $r)->getCalculatedValue(), 2),
                    'tunjangan_jabatan'            => round((float) $sheet->getCell('J' . $r)->getCalculatedValue(), 2),
                    'tunjangan_kompetensi'         => round((float) $sheet->getCell('K' . $r)->getCalculatedValue(), 2),
                    'tunjangan_intensif'           => round((float) $sheet->getCell('L' . $r)->getCalculatedValue(), 2),
                    'tunjangan_makan'              => round((float) $sheet->getCell('M' . $r)->getCalculatedValue(), 2),
                    'tunjangan_hadir'              => round((float) $sheet->getCell('N' . $r)->getCalculatedValue(), 2),
                    'nama_bank'                    => trim((string) $sheet->getCell('O' . $r)->getValue()) ?: null,
                    'no_rekening'                  => trim((string) $sheet->getCell('P' . $r)->getValue()) ?: null,
                    'rekening_atas_nama'           => trim((string) $sheet->getCell('Q' . $r)->getValue()) ?: null,
                    'bpjs_ketenagakerjaan'         => trim((string) $sheet->getCell('R' . $r)->getValue()) ?: null,
                    'bpjs_ketenagakerjaan_nominal' => round((float) $sheet->getCell('S' . $r)->getCalculatedValue(), 2),
                    'bpjs_kesehatan'               => trim((string) $sheet->getCell('T' . $r)->getValue()) ?: null,
                    'bpjs_kesehatan_nominal'       => round((float) $sheet->getCell('U' . $r)->getCalculatedValue(), 2),
                    'nik'                          => trim((string) $sheet->getCell('V' . $r)->getValue()) ?: null,
                    'no_telp'                      => trim((string) $sheet->getCell('W' . $r)->getValue()) ?: null,
                    'tanggal_masuk_kerja'          => $parseDate($sheet->getCell('X' . $r)->getValue()),
                    'tangal_akhir_kontrak'         => $parseDate($sheet->getCell('Y' . $r)->getValue()),
                    'masa_kontrak'                 => ($mk = strtoupper(trim((string) $sheet->getCell('Z' . $r)->getValue()))) !== '' && array_key_exists($mk, Karyawan::MASA_KONTRAK_OPTIONS) ? $mk : null,
                ];

                $existing = $nip !== ''
                    ? Karyawan::where('nip', $nip)->first()
                    : Karyawan::where('nama', 'like', $nama)->first();

                if ($existing) {
                    $existing->update($payload);
                    $updated++;
                } else {
                    Karyawan::create($payload);
                    $created++;
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'total'   => $created + $updated,
        ];
    }
}
