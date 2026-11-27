<?php

namespace App\Services\HR;

use App\Models\Gaji;
use App\Models\Karyawan;
use App\Models\MasterDepartemen;
use App\Models\PayrollBorongan;
use App\Models\PayrollBoronganItem;
use App\Models\PengPinjaman;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PayrollExcelImportService
{
    public function __construct(
        protected PayrollService $payrollService
    ) {}

    /**
     * Parse and import payroll from uploaded Excel file (.xlsx, .xls).
     *
     * @param string $filePath Absolute path or storage path of Excel file
     * @param array $options [periode => 'YYYY-MM', status => 'Draft'|'Approved', auto_create_karyawan => bool, tipe_payroll => 'Bulanan']
     * @return array Summary of import operation
     */
    public function importFromSpreadsheet(string $filePath, array $options = []): array
    {
        if (! file_exists($filePath)) {
            throw new \InvalidArgumentException("File Excel tidak ditemukan pada path: {$filePath}");
        }

        $spreadsheet = $this->loadSpreadsheet($filePath);
        $sheetNames = $spreadsheet->getSheetNames();

        // Check if multi-sheet TTC workbook format
        $hasSummarySheet = in_array('Gaji yang diterima', $sheetNames, true);

        if ($hasSummarySheet) {
            return $this->importTtcWorkbookFormat($spreadsheet, $options);
        }

        return $this->importGenericSheetFormat($spreadsheet, $options);
    }

    /**
     * Load file Excel (.xlsx, .xls) maupun CSV (.csv dengan delimiter ',' atau ';') secara aman.
     */
    protected function loadSpreadsheet(string $filePath): Spreadsheet
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $isCsv = in_array($ext, ['csv', 'txt'], true);

        if (! $isCsv) {
            try {
                $identified = IOFactory::identify($filePath);
                $isCsv = ($identified === 'Csv');
            } catch (\Throwable) {
                $isCsv = false;
            }
        }

        if ($isCsv) {
            $sample = '';
            $fh = @fopen($filePath, 'rb');
            if ($fh) {
                for ($i = 0; $i < 10 && ! feof($fh); $i++) {
                    $sample .= (string) fgets($fh);
                }
                fclose($fh);
            }
            $commaCount = substr_count($sample, ',');
            $semiCount = substr_count($sample, ';');
            $tabCount = substr_count($sample, "\t");

            $delimiter = ',';
            if ($semiCount > $commaCount && $semiCount >= $tabCount) {
                $delimiter = ';';
            } elseif ($tabCount > $commaCount && $tabCount > $semiCount) {
                $delimiter = "\t";
            }

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            $reader->setDelimiter($delimiter);
            $reader->setEnclosure('"');
            $reader->setInputEncoding('UTF-8');

            return $reader->load($filePath);
        }

        return IOFactory::load($filePath);
    }

    /**
     * Membaca nilai numerik dari sel Excel/CSV secara aman (tahan error formula & format "Rp 3.800.000").
     */
    protected function readNumericCell(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $coord): float
    {
        $cell = $sheet->getCell($coord);

        try {
            $raw = $cell->getCalculatedValue();
        } catch (\Throwable) {
            $raw = $cell->getOldCalculatedValue();
            if ($raw === null) {
                $raw = $cell->getValue();
            }
        }

        if ($raw === null || $raw === '' || is_bool($raw)) {
            return 0.0;
        }

        if (is_int($raw) || is_float($raw)) {
            return (float) $raw;
        }

        $str = trim((string) $raw);
        if ($str === '' || $str === '-' || str_starts_with($str, '#')) {
            return 0.0;
        }

        // Bersihkan prefix mata uang & spasi
        $str = preg_replace('/^Rp\.?\s*/i', '', $str) ?? $str;
        $str = str_replace([' ', "\xc2\xa0"], '', $str);

        if (is_numeric($str)) {
            return (float) $str;
        }

        // Format Indonesia (3.800.000 atau 3.800.000,50) vs US (3,800,000.50)
        $hasDot = str_contains($str, '.');
        $hasComma = str_contains($str, ',');

        if ($hasDot && $hasComma) {
            if (strrpos($str, ',') > strrpos($str, '.')) {
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            } else {
                $str = str_replace(',', '', $str);
            }
        } elseif ($hasDot && preg_match('/^-?\d{1,3}(\.\d{3})+$/', $str)) {
            $str = str_replace('.', '', $str);
        } elseif ($hasComma && preg_match('/^-?\d{1,3}(,\d{3})+$/', $str)) {
            $str = str_replace(',', '', $str);
        } elseif ($hasComma) {
            $str = str_replace(',', '.', $str);
        }

        return is_numeric($str) ? (float) $str : 0.0;
    }

    /**
     * Dapatkan departemen default secara aman (tidak pernah gagal FK jika tabel departemen kosong).
     */
    protected function resolveDefaultDepartemenId(): int
    {
        $dept = MasterDepartemen::first();
        if ($dept) {
            return (int) $dept->id;
        }

        return (int) MasterDepartemen::create([
            'kode'            => 'UMU',
            'nama_departemen' => 'Umum',
            'deskripsi'       => 'Departemen default hasil impor payroll',
            'status'          => 'Aktif',
        ])->id;
    }

    /**
     * Generate NIP unik otomatis jika kolom NIP/Kode kosong.
     */
    protected function generateUniqueNip(): string
    {
        $seq = Karyawan::count() + 1;
        do {
            $candidate = 'K' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
            $seq++;
        } while (Karyawan::where('nip', $candidate)->exists());

        return $candidate;
    }

    /**
     * Importer khusus struktur Workbook Resmi Bulanan TTC (seperti 11._NOVEMBER_PAYROLL_2026_TTC.xlsx).
     */
    protected function importTtcWorkbookFormat(Spreadsheet $spreadsheet, array $options): array
    {
        $sheetSummary = $spreadsheet->getSheetByName('Gaji yang diterima');
        $sheetJabatan = $spreadsheet->getSheetByName('Jabatan dan Gaji');

        // 1. Ekstraksi Periode & Tanggal Cut-off
        $detectedPeriode = null;
        $tanggalMulai = null;
        $tanggalSelesai = null;

        // Cek dari Subtitle Sheet Summary (contoh: "BULAN NOVEMBER 2026")
        $subtitleSummary = trim((string) $sheetSummary->getCell('A2')->getValue());
        if (preg_match('/(?:BULAN\s+)?([A-Za-z]+)\s+(\d{4})/i', $subtitleSummary, $m)) {
            $monthNum = $this->parseIndonesianMonth($m[1]);
            if ($monthNum) {
                $detectedPeriode = sprintf('%04d-%02d', (int) $m[2], $monthNum);
            }
        }

        // Cek tanggal cut-off dari 'Jabatan dan Gaji' (contoh: "PERIODE 26 OKTOBER - 25 NOVEMBER 2026")
        if ($sheetJabatan) {
            $periodText = trim((string) $sheetJabatan->getCell('A2')->getValue());
            if (preg_match('/(\d{1,2})\s+([A-Za-z]+)\s*-\s*(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})/i', $periodText, $pm)) {
                $mStart = $this->parseIndonesianMonth($pm[2]);
                $mEnd = $this->parseIndonesianMonth($pm[4]);
                $year = (int) $pm[5];
                if ($mStart && $mEnd) {
                    $yStart = ($mStart > $mEnd) ? $year - 1 : $year;
                    $tanggalMulai = sprintf('%04d-%02d-%02d', $yStart, $mStart, (int) $pm[1]);
                    $tanggalSelesai = sprintf('%04d-%02d-%02d', $year, $mEnd, (int) $pm[3]);
                }
            }
        }

        $periode = ! empty($options['periode']) ? trim((string) $options['periode']) : ($detectedPeriode ?: now()->format('Y-m'));
        if (! $tanggalMulai || ! $tanggalSelesai) {
            try {
                $cDate = Carbon::createFromFormat('Y-m', $periode)->startOfMonth();
                $tanggalMulai = $cDate->toDateString();
                $tanggalSelesai = $cDate->copy()->endOfMonth()->toDateString();
            } catch (\Throwable) {
                $tanggalMulai = now()->startOfMonth()->toDateString();
                $tanggalSelesai = now()->endOfMonth()->toDateString();
            }
        }

        $statusSlip = $options['status'] ?? 'Draft';
        $autoCreateKaryawan = $options['auto_create_karyawan'] ?? true;
        $tipePayroll = $options['tipe_payroll'] ?? 'Bulanan';

        // 2. Baca Kamus Master Karyawan dari sheet 'Jabatan dan Gaji'
        $employeeMaster = [];
        if ($sheetJabatan) {
            $highestRowJab = $sheetJabatan->getHighestRow();
            for ($r = 4; $r <= $highestRowJab; $r++) {
                $kode = trim((string) $sheetJabatan->getCell('B' . $r)->getValue());
                $nama = trim((string) $sheetJabatan->getCell('C' . $r)->getValue());
                $jabatan = trim((string) $sheetJabatan->getCell('D' . $r)->getValue());
                $nik = trim((string) $sheetJabatan->getCell('L' . $r)->getValue());
                if ($kode !== '' && $nama !== '' && $nama !== '#N/A') {
                    $employeeMaster[$kode] = [
                        'nama'    => $nama,
                        'jabatan' => $jabatan ?: 'Staff Umum',
                        'nik'     => $nik ?: null,
                    ];
                }
            }
        }

        // 2b. Baca Porsi BPJS Perusahaan (3,7%) dari sheet 'KETENAGAKERJAAN'
        $bpjsPerusahaanMap = [];
        $sheetBpjs = $spreadsheet->getSheetByName('KETENAGAKERJAAN');
        if ($sheetBpjs) {
            for ($r = 4; $r <= $sheetBpjs->getHighestRow(); $r++) {
                $kodeBpjs = trim((string) $sheetBpjs->getCell('B' . $r)->getValue());
                if ($kodeBpjs !== '' && strtoupper($kodeBpjs) !== 'TOTAL') {
                    $bpjsPtVal = round($this->readNumericCell($sheetBpjs, 'E' . $r), 2);
                    if ($bpjsPtVal > 0) {
                        $bpjsPerusahaanMap[$kodeBpjs] = $bpjsPtVal;
                    }
                }
            }
        }

        // 2c. Baca Detail Pinjaman/Kasbon dari sheet 'Kasbon'
        $kasbonSheetMap = [];
        $sheetKasbon = $spreadsheet->getSheetByName('Kasbon');
        if ($sheetKasbon) {
            for ($r = 4; $r <= $sheetKasbon->getHighestRow(); $r++) {
                $kodeKb = trim((string) $sheetKasbon->getCell('B' . $r)->getValue());
                $cicilanKb = round($this->readNumericCell($sheetKasbon, 'E' . $r), 2);
                $ketKb = trim((string) $sheetKasbon->getCell('G' . $r)->getValue());
                if ($kodeKb !== '' && $cicilanKb > 0) {
                    $totalHutang = $cicilanKb;
                    if (preg_match('/hutang\s+Rp\.?\s*([\d\.,]+)/i', $ketKb, $hm)) {
                        $clean = (float) str_replace(['.', ','], '', $hm[1]);
                        if ($clean > 0) {
                            $totalHutang = $clean;
                        }
                    }
                    $tenor = 1;
                    if (preg_match('/(\d+)\s*x\s*pembayaran/i', $ketKb, $tm)) {
                        $tenor = max(1, (int) $tm[1]);
                    } elseif ($cicilanKb > 0) {
                        $tenor = max(1, (int) round($totalHutang / $cicilanKb));
                    }
                    $kasbonSheetMap[$kodeKb] = [
                        'nilai_pinjaman'    => $totalHutang,
                        'cicilan_per_bulan' => $cicilanKb,
                        'jangka_waktu'      => $tenor,
                        'keterangan'        => $ketKb ?: "Impor Kasbon Excel {$periode}",
                    ];
                }
            }
        }

        // 3. Iterasi baris data penggajian pada sheet 'Gaji yang diterima'
        $highestRowSummary = $sheetSummary->getHighestRow();
        $importedCount = 0;
        $updatedCount = 0;
        $karyawanCreatedCount = 0;
        $totalThp = 0.0;
        $errors = [];
        $processedRecords = [];

        DB::beginTransaction();
        try {
            for ($r = 6; $r <= $highestRowSummary; $r++) {
                $kode = trim((string) $sheetSummary->getCell('B' . $r)->getValue());
                $namaCell = trim((string) $sheetSummary->getCell('C' . $r)->getValue());

                // Skip header, blank, formula error, atau baris TOTAL
                if (empty($kode) || strtoupper($kode) === 'TOTAL' || $namaCell === '#N/A') {
                    continue;
                }

                // Ambil nama nyata (utamakan dari kamus Jabatan dan Gaji jika cell C adalah formula VLOOKUP)
                $realNama = ! empty($employeeMaster[$kode]['nama'])
                    ? $employeeMaster[$kode]['nama']
                    : (str_starts_with($namaCell, '=') ? null : $namaCell);

                if (empty($realNama)) {
                    // Coba hitung formula jika nama masih null
                    $calcVal = $sheetSummary->getCell('C' . $r)->getCalculatedValue();
                    if ($calcVal && $calcVal !== '#N/A' && ! str_starts_with((string) $calcVal, '=')) {
                        $realNama = trim((string) $calcVal);
                    }
                }

                if (empty($realNama)) {
                    continue;
                }

                $gp = $this->readNumericCell($sheetSummary, 'D' . $r);
                $tKompetensi = $this->readNumericCell($sheetSummary, 'E' . $r);
                $tJabatan = $this->readNumericCell($sheetSummary, 'F' . $r);
                $tHadir = $this->readNumericCell($sheetSummary, 'G' . $r);
                $lembur = $this->readNumericCell($sheetSummary, 'H' . $r);
                $bonus = $this->readNumericCell($sheetSummary, 'I' . $r);
                $tLain = $this->readNumericCell($sheetSummary, 'J' . $r);

                $potAbsen = $this->readNumericCell($sheetSummary, 'K' . $r);
                $potTerlambat = $this->readNumericCell($sheetSummary, 'L' . $r);
                $potKasbon = $this->readNumericCell($sheetSummary, 'M' . $r);
                $potTabungan = $this->readNumericCell($sheetSummary, 'N' . $r);
                $potTarget = $this->readNumericCell($sheetSummary, 'O' . $r);
                $potBpjs = $this->readNumericCell($sheetSummary, 'P' . $r);
                $thpExcel = $this->readNumericCell($sheetSummary, 'Q' . $r);

                // Skip baris template kosong (misal K08-K32 yang GP dan THP-nya 0)
                if ($gp <= 0 && $thpExcel <= 0 && $tJabatan <= 0 && $tKompetensi <= 0) {
                    continue;
                }

                // 4. Cari atau Buat Karyawan
                $karyawan = Karyawan::where('nip', $kode)
                    ->orWhere('nama', 'like', $realNama)
                    ->first();

                if (! $karyawan) {
                    if (! $autoCreateKaryawan) {
                        $errors[] = "Baris {$r}: Karyawan [{$kode}] {$realNama} belum terdaftar di sistem.";
                        continue;
                    }

                    $defaultDept = $this->resolveDefaultDepartemenId();
                    $karyawan = Karyawan::create([
                        'nip'                          => $kode,
                        'nama'                         => $realNama,
                        'departemen_id'                => $defaultDept,
                        'nik'                          => $employeeMaster[$kode]['nik'] ?? null,
                        'jabatan'                      => $employeeMaster[$kode]['jabatan'] ?? 'Staff Umum',
                        'status_pegawai'               => 'Aktif',
                        'tipe_gaji'                    => $tipePayroll,
                        'gaji_pokok'                   => $gp,
                        'tunjangan_kompetensi'         => $tKompetensi,
                        'tunjangan_jabatan'            => $tJabatan,
                        'tunjangan_intensif'           => $tHadir,
                        'bpjs_ketenagakerjaan_nominal' => $potBpjs,
                    ]);
                    $karyawanCreatedCount++;
                }

                // 5. Hitung Komponen Gaji & Porsi BPJS Perusahaan (3,7%)
                $totPendapatan = round($gp + $tJabatan + $tKompetensi + $tHadir + $lembur + $bonus + $tLain, 2);
                $potKehadiran = round($potAbsen + $potTerlambat, 2);
                $potLainLain = round($potTabungan + $potTarget, 2);
                $totPotongan = round($potKehadiran + $potKasbon + $potBpjs + $potLainLain, 2);
                $gajiBersih = max(0.0, round($totPendapatan - $totPotongan, 2));
                $bpjsPerusahaan = $bpjsPerusahaanMap[$kode] ?? ($potBpjs > 0 && $gp > 0 ? round($gp * 0.037, 2) : 0.0);

                // Sinkronkan master Pinjaman/Kasbon dari sheet Kasbon bila karyawan belum punya pinjaman aktif
                if ($potKasbon > 0 && ($options['sync_kasbon'] ?? true) && isset($kasbonSheetMap[$kode])) {
                    $hasActiveLoan = PengPinjaman::where('karyawan_id', $karyawan->id)
                        ->where(function ($q) {
                            $q->whereNull('status')->orWhere('status', '!=', 'Rejected');
                        })
                        ->exists();

                    if (! $hasActiveLoan) {
                        $kbInfo = $kasbonSheetMap[$kode];
                        PengPinjaman::create([
                            'karyawan_id'        => $karyawan->id,
                            'kode_gudang'        => 'TTC',
                            'tanggal_pengajuan'  => $tanggalMulai,
                            'nilai_pinjaman'     => $kbInfo['nilai_pinjaman'],
                            'jangka_waktu'       => $kbInfo['jangka_waktu'],
                            'cicilan_per_bulan'  => $kbInfo['cicilan_per_bulan'],
                            'keperluan_pinjaman' => $kbInfo['keterangan'],
                            'status'             => 'Approved',
                            'approved_by'        => 'Impor Excel Payroll',
                            'approved_at'        => now(),
                            'is_disbursed'       => true,
                            'post_to_ledger'     => false,
                        ]);
                    }
                }

                // 6. Cek apakah slip sudah ada
                $existingGaji = Gaji::where('karyawan_id', $karyawan->id)
                    ->where('periode', $periode)
                    ->where('tipe_payroll', $tipePayroll)
                    ->first();

                $payload = [
                    'karyawan_id'          => $karyawan->id,
                    'periode'              => $periode,
                    'tipe_payroll'         => $tipePayroll,
                    'tanggal_mulai'        => $tanggalMulai,
                    'tanggal_selesai'      => $tanggalSelesai,
                    'tanggal_pembayaran'   => $tanggalSelesai,
                    'metode_pembayaran'    => 'Transfer Bank',
                    'gaji_pokok'           => $gp,
                    'tunjangan_jabatan'    => $tJabatan,
                    'tunjangan_kompetensi' => $tKompetensi,
                    'tunjangan_intensif'   => $tHadir,
                    'tunjangan_makan'      => 0,
                    'tunjangan_hadir'      => 0,
                    'tunjangan_lain'       => $tLain,
                    'total_lembur'         => $lembur,
                    'total_bonus'          => $bonus,
                    'total_pendapatan'     => $totPendapatan,
                    'potongan_tunjangan'   => 0,
                    'potongan_kehadiran'   => $potKehadiran,
                    'potongan_pinjaman'    => $potKasbon,
                    'potongan_bpjs'        => $potBpjs,
                    'bpjs_perusahaan'      => $bpjsPerusahaan,
                    'potongan_pph21'       => 0,
                    'potongan_lain'        => $potLainLain,
                    'total_potongan'       => $totPotongan,
                    'gaji_bersih'          => $gajiBersih,
                    'status'               => $statusSlip,
                    'post_to_ledger'       => false,
                    'keterangan'           => "Impor Excel TTC {$periode} [{$kode}]",
                ];

                if ($existingGaji) {
                    $existingGaji->update($payload);
                    $gajiRecord = $existingGaji;
                    $updatedCount++;
                } else {
                    $payload['kode_slip'] = $this->payrollService->generateKodeSlip(Carbon::parse($tanggalSelesai));
                    $gajiRecord = Gaji::create($payload);
                    $importedCount++;
                }

                $totalThp += $gajiBersih;
                $processedRecords[] = [
                    'kode'        => $kode,
                    'nama'        => $realNama,
                    'gaji_pokok'  => $gp,
                    'gaji_bersih' => $gajiBersih,
                    'slip_id'     => $gajiRecord->id,
                    'kode_slip'   => $gajiRecord->kode_slip,
                ];
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'success'                => true,
            'periode'                => $periode,
            'tanggal_mulai'          => $tanggalMulai,
            'tanggal_selesai'        => $tanggalSelesai,
            'imported_count'         => $importedCount,
            'updated_count'          => $updatedCount,
            'karyawan_created_count' => $karyawanCreatedCount,
            'total_records'          => $importedCount + $updatedCount,
            'total_thp'              => $totalThp,
            'errors'                 => $errors,
            'processed'              => $processedRecords,
        ];
    }

    /**
     * Importer untuk sheet datar umum (Single Sheet format tabel standar).
     */
    protected function importGenericSheetFormat(Spreadsheet $spreadsheet, array $options): array
    {
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();
        $colLimit = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

        // Cari baris header
        $headerRow = 1;
        $colMap = [];
        for ($r = 1; $r <= min(15, $highestRow); $r++) {
            $headers = [];
            for ($c = 1; $c <= $colLimit; $c++) {
                $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
                $val = strtolower(trim((string) $sheet->getCell($colLet . $r)->getValue()));
                if ($val) {
                    $headers[$colLet] = $val;
                }
            }
            if ($this->isHeaderRow($headers)) {
                $headerRow = $r;
                $colMap = $this->mapColumns($headers);
                break;
            }
        }

        if (empty($colMap['nama'])) {
            throw ValidationException::withMessages([
                'file' => 'Kolom "Nama Karyawan" tidak ditemukan dalam baris header tabel Excel/CSV.',
            ]);
        }

        // Auto-deteksi periode dari baris judul (misal "PERIODE: 2026-11" atau "BULAN NOVEMBER 2026") bila input periode dikosongkan
        $detectedPeriode = null;
        for ($pr = 1; $pr < $headerRow; $pr++) {
            for ($pc = 1; $pc <= min(6, $colLimit); $pc++) {
                $pCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($pc);
                $pText = trim((string) $sheet->getCell($pCol . $pr)->getValue());
                if ($pText === '') {
                    continue;
                }
                if (preg_match('/(20\d{2})[-\/](0[1-9]|1[0-2])/', $pText, $pm)) {
                    $detectedPeriode = $pm[1] . '-' . $pm[2];
                    break 2;
                }
                if (preg_match('/(?:BULAN|PERIODE)\s*:?\s*([A-Za-z]+)\s+(20\d{2})/i', $pText, $pm)) {
                    $mNum = $this->parseIndonesianMonth($pm[1]);
                    if ($mNum) {
                        $detectedPeriode = sprintf('%04d-%02d', (int) $pm[2], $mNum);
                        break 2;
                    }
                }
            }
        }

        $periode = ! empty($options['periode']) ? trim((string) $options['periode']) : ($detectedPeriode ?: now()->format('Y-m'));
        $statusSlip = $options['status'] ?? 'Draft';
        $autoCreateKaryawan = $options['auto_create_karyawan'] ?? true;
        $tipePayroll = $options['tipe_payroll'] ?? 'Bulanan';

        $tanggalMulai = now()->startOfMonth()->toDateString();
        $tanggalSelesai = now()->endOfMonth()->toDateString();
        try {
            $cDate = Carbon::createFromFormat('Y-m', $periode)->startOfMonth();
            $tanggalMulai = $cDate->toDateString();
            $tanggalSelesai = $cDate->copy()->endOfMonth()->toDateString();
        } catch (\Throwable) {}

        $importedCount = 0;
        $updatedCount = 0;
        $karyawanCreatedCount = 0;
        $totalThp = 0.0;
        $errors = [];
        $processedRecords = [];

        DB::beginTransaction();
        try {
            for ($r = $headerRow + 1; $r <= $highestRow; $r++) {
                $nama = trim((string) $sheet->getCell(($colMap['nama'] ?? 'A') . $r)->getValue());
                if (empty($nama) || strtoupper($nama) === 'TOTAL' || str_starts_with(strtoupper($nama), 'JUMLAH')) {
                    continue;
                }

                $kode = ! empty($colMap['kode']) ? trim((string) $sheet->getCell($colMap['kode'] . $r)->getValue()) : null;
                $jabatan = ! empty($colMap['jabatan']) ? trim((string) $sheet->getCell($colMap['jabatan'] . $r)->getValue()) : 'Staff';
                if ($jabatan === '' || is_numeric($jabatan)) {
                    $jabatan = 'Staff Umum';
                }

                $getVal = function (?string $col) use ($sheet, $r): float {
                    if (! $col) {
                        return 0.0;
                    }

                    return $this->readNumericCell($sheet, $col . $r);
                };

                $gp = $getVal($colMap['gaji_pokok'] ?? null);
                $tJabatan = $getVal($colMap['tunjangan_jabatan'] ?? null);
                $tKompetensi = $getVal($colMap['tunjangan_kompetensi'] ?? null);
                $tIntensif = $getVal($colMap['tunjangan_intensif'] ?? null);
                $tMakan = $getVal($colMap['tunjangan_makan'] ?? null);
                $tHadir = $getVal($colMap['tunjangan_hadir'] ?? null);
                $lembur = $getVal($colMap['total_lembur'] ?? null);
                $bonus = $getVal($colMap['total_bonus'] ?? null);
                $tLain = $getVal($colMap['tunjangan_lain'] ?? null);

                $potHadir = $getVal($colMap['potongan_kehadiran'] ?? null);
                $potPinjaman = $getVal($colMap['potongan_pinjaman'] ?? null);
                $potBpjs = $getVal($colMap['potongan_bpjs'] ?? null);
                $potPph21 = $getVal($colMap['potongan_pph21'] ?? null);
                $potLain = $getVal($colMap['potongan_lain'] ?? null);
                $bpjsPerusahaan = ! empty($colMap['bpjs_perusahaan'])
                    ? $getVal($colMap['bpjs_perusahaan'])
                    : ($potBpjs > 0 && $gp > 0 ? round($gp * 0.037, 2) : 0.0);

                $totPendapatan = round($gp + $tJabatan + $tKompetensi + $tIntensif + $tMakan + $tHadir + $lembur + $bonus + $tLain, 2);
                $totPotongan = round($potHadir + $potPinjaman + $potBpjs + $potPph21 + $potLain, 2);
                $gajiBersih = max(0.0, round($totPendapatan - $totPotongan, 2));

                if ($gp <= 0 && $gajiBersih <= 0 && $totPendapatan <= 0) {
                    continue;
                }

                $karyawanQuery = Karyawan::query();
                if ($kode) {
                    $karyawanQuery->where('nip', $kode)->orWhere('nama', 'like', $nama);
                } else {
                    $karyawanQuery->where('nama', 'like', $nama);
                }
                $karyawan = $karyawanQuery->first();

                if (! $karyawan) {
                    if (! $autoCreateKaryawan) {
                        $errors[] = "Baris {$r}: Karyawan {$nama} tidak ditemukan.";
                        continue;
                    }

                    $defaultDept = $this->resolveDefaultDepartemenId();
                    $karyawan = Karyawan::create([
                        'nip'                          => $kode ?: $this->generateUniqueNip(),
                        'nama'                         => $nama,
                        'departemen_id'                => $defaultDept,
                        'jabatan'                      => $jabatan,
                        'status_pegawai'               => 'Aktif',
                        'tipe_gaji'                    => $tipePayroll,
                        'gaji_pokok'                   => $gp,
                        'tunjangan_jabatan'            => $tJabatan,
                        'tunjangan_kompetensi'         => $tKompetensi,
                        'tunjangan_intensif'           => $tIntensif,
                        'tunjangan_makan'              => $tMakan,
                        'tunjangan_hadir'              => $tHadir,
                        'bpjs_ketenagakerjaan_nominal' => $potBpjs,
                    ]);
                    $karyawanCreatedCount++;
                }

                $existingGaji = Gaji::where('karyawan_id', $karyawan->id)
                    ->where('periode', $periode)
                    ->where('tipe_payroll', $tipePayroll)
                    ->first();

                $payload = [
                    'karyawan_id'          => $karyawan->id,
                    'periode'              => $periode,
                    'tipe_payroll'         => $tipePayroll,
                    'tanggal_mulai'        => $tanggalMulai,
                    'tanggal_selesai'      => $tanggalSelesai,
                    'tanggal_pembayaran'   => $tanggalSelesai,
                    'metode_pembayaran'    => 'Transfer Bank',
                    'gaji_pokok'           => $gp,
                    'tunjangan_jabatan'    => $tJabatan,
                    'tunjangan_kompetensi' => $tKompetensi,
                    'tunjangan_intensif'   => $tIntensif,
                    'tunjangan_makan'      => $tMakan,
                    'tunjangan_hadir'      => $tHadir,
                    'tunjangan_lain'       => $tLain,
                    'total_lembur'         => $lembur,
                    'total_bonus'          => $bonus,
                    'total_pendapatan'     => $totPendapatan,
                    'potongan_tunjangan'   => 0,
                    'potongan_kehadiran'   => $potHadir,
                    'potongan_pinjaman'    => $potPinjaman,
                    'potongan_bpjs'        => $potBpjs,
                    'bpjs_perusahaan'      => $bpjsPerusahaan,
                    'potongan_pph21'       => $potPph21,
                    'potongan_lain'        => $potLain,
                    'total_potongan'       => $totPotongan,
                    'gaji_bersih'          => $gajiBersih,
                    'status'               => $statusSlip,
                    'post_to_ledger'       => false,
                    'keterangan'           => "Impor Excel Standar {$periode}",
                ];

                if ($existingGaji) {
                    $existingGaji->update($payload);
                    $gajiRecord = $existingGaji;
                    $updatedCount++;
                } else {
                    $payload['kode_slip'] = $this->payrollService->generateKodeSlip(Carbon::parse($tanggalSelesai));
                    $gajiRecord = Gaji::create($payload);
                    $importedCount++;
                }

                $totalThp += $gajiBersih;
                $processedRecords[] = [
                    'kode'        => $kode ?: $karyawan->nip,
                    'nama'        => $nama,
                    'gaji_pokok'  => $gp,
                    'gaji_bersih' => $gajiBersih,
                    'slip_id'     => $gajiRecord->id,
                    'kode_slip'   => $gajiRecord->kode_slip,
                ];
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'success'                => true,
            'periode'                => $periode,
            'tanggal_mulai'          => $tanggalMulai,
            'tanggal_selesai'        => $tanggalSelesai,
            'imported_count'         => $importedCount,
            'updated_count'          => $updatedCount,
            'karyawan_created_count' => $karyawanCreatedCount,
            'total_records'          => $importedCount + $updatedCount,
            'total_thp'              => $totalThp,
            'errors'                 => $errors,
            'processed'              => $processedRecords,
        ];
    }

    /**
     * Generate file template Excel resmi untuk input dan rekap payroll.
     */
    public function generateTemplateSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Payroll');

        // Header Dokumen
        $sheet->setCellValue('A1', 'FORMAT IMPOR PAYROLL KARYAWAN PT. TEKNOGRAFI TRI CAWANASKA');
        $sheet->setCellValue('A2', 'PERIODE: ' . now()->format('Y-m') . ' (Silakan disesuaikan)');
        $sheet->mergeCells('A1:Q1');
        $sheet->mergeCells('A2:Q2');
        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(13);

        // Header Kolom Tabel
        $headers = [
            'A4' => 'No',
            'B4' => 'Kode Karyawan',
            'C4' => 'Nama Karyawan',
            'D4' => 'Jabatan',
            'E4' => 'Gaji Pokok',
            'F4' => 'T. Jabatan',
            'G4' => 'T. Kompetensi',
            'H4' => 'Insentif Kehadiran',
            'I4' => 'Uang Lembur',
            'J4' => 'Bonus / Tambahan',
            'K4' => 'Tunjangan Lain',
            'L4' => 'Pot. Kehadiran (Alpha/Telat)',
            'M4' => 'Pot. Kasbon / Pinjaman',
            'N4' => 'Pot. BPJS Ketenagakerjaan',
            'O4' => 'Potongan Lain-lain',
            'P4' => 'Total Potongan',
            'Q4' => 'Take Home Pay (Gaji Bersih)',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A4:Q4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(28);

        // Baris Contoh Data
        $sampleData = [
            [1, 'K01', 'Heri Purnomo', 'PPIC', 3800000, 0, 0, 0, 0, 0, 0, 0, 0, 76000, 0],
            [2, 'K02', 'Dliyaul Fuad', 'Staff Umum', 2060000, 0, 60000, 0, 0, 0, 0, 0, 390000, 41200, 0],
            [3, 'K03', 'Sunarni', 'Staff Umum', 2605000, 705000, 0, 300000, 0, 0, 0, 0, 0, 52100, 0],
            [4, 'K04', 'Muhammad Najwal Halim', 'Staff Umum', 2587000, 0, 187000, 300000, 0, 0, 0, 0, 1100000, 51740, 0],
            [5, 'K05', 'Inamul Hasan', 'Staff Umum', 3800000, 0, 0, 0, 0, 0, 0, 1976000, 0, 76000, 0],
            [6, 'K06', 'Maulana Alvian Sulistyo', 'Staff Umum', 3000000, 0, 0, 0, 0, 0, 0, 0, 0, 60000, 0],
            [7, 'K07', 'Priska', 'Staff Umum', 2000000, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        ];

        $rowNum = 5;
        foreach ($sampleData as $row) {
            $sheet->setCellValue('A' . $rowNum, $row[0]);
            $sheet->setCellValue('B' . $rowNum, $row[1]);
            $sheet->setCellValue('C' . $rowNum, $row[2]);
            $sheet->setCellValue('D' . $rowNum, $row[3]);
            $sheet->setCellValue('E' . $rowNum, $row[4]);
            $sheet->setCellValue('F' . $rowNum, $row[5]);
            $sheet->setCellValue('G' . $rowNum, $row[6]);
            $sheet->setCellValue('H' . $rowNum, $row[7]);
            $sheet->setCellValue('I' . $rowNum, $row[8]);
            $sheet->setCellValue('J' . $rowNum, $row[9]);
            $sheet->setCellValue('K' . $rowNum, $row[10]);
            $sheet->setCellValue('L' . $rowNum, $row[11]);
            $sheet->setCellValue('M' . $rowNum, $row[12]);
            $sheet->setCellValue('N' . $rowNum, $row[13]);
            $sheet->setCellValue('O' . $rowNum, $row[14]);
            // Formula Total Potongan = L + M + N + O
            $sheet->setCellValue('P' . $rowNum, "=SUM(L{$rowNum}:O{$rowNum})");
            // Formula Take Home Pay = SUM(E:K) - P
            $sheet->setCellValue('Q' . $rowNum, "=SUM(E{$rowNum}:K{$rowNum})-P{$rowNum}");

            $sheet->getStyle("E{$rowNum}:Q{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
            $rowNum++;
        }

        // Auto width untuk seluruh kolom
        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    /**
     * Import Payroll Borongan dari File Excel.
     */
    public function importBoronganSpreadsheet(string $filePath, array $options = []): array
    {
        if (! file_exists($filePath)) {
            throw new \InvalidArgumentException("File Excel tidak ditemukan: {$filePath}");
        }

        $spreadsheet = $this->loadSpreadsheet($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        // Cari header row
        $headerRow = 1;
        for ($r = 1; $r <= min(15, $highestRow); $r++) {
            $rowText = '';
            for ($c = 1; $c <= 15; $c++) {
                $colLet = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
                $rowText .= ' ' . strtolower(trim((string) $sheet->getCell($colLet . $r)->getValue()));
            }
            if (str_contains($rowText, 'nama') && (str_contains($rowText, 'pekerjaan') || str_contains($rowText, 'protol') || str_contains($rowText, 'packing') || str_contains($rowText, 'total'))) {
                $headerRow = $r;
                break;
            }
        }

        $periode = ! empty($options['periode']) ? trim((string) $options['periode']) : now()->format('Y-m');
        $status = $options['status'] ?? 'Draft';
        $tglTransaksi = $options['tanggal_transaksi'] ?? now()->toDateString();
        $kodeGudang = $options['kode_gudang'] ?? 'TTC';

        $items = [];
        $totBorongan = 0.0;
        $totTunjangan = 0.0;
        $totPotongan = 0.0;

        for ($r = $headerRow + 1; $r <= $highestRow; $r++) {
            $nama = trim((string) $sheet->getCell('B' . $r)->getValue());
            if (empty($nama) || strtoupper($nama) === 'TOTAL') {
                continue;
            }

            $nik = trim((string) $sheet->getCell('C' . $r)->getValue()) ?: null;
            $jenis = trim((string) $sheet->getCell('D' . $r)->getValue()) ?: 'Borongan Produksi';
            $protolQty = $this->readNumericCell($sheet, 'E' . $r);
            $protolTarif = $this->readNumericCell($sheet, 'F' . $r);
            $protolGaji = round($protolQty * $protolTarif, 2);

            $packQty = $this->readNumericCell($sheet, 'G' . $r);
            $packTarif = $this->readNumericCell($sheet, 'H' . $r);
            $packGaji = round($packQty * $packTarif, 2);

            $lainQty = $this->readNumericCell($sheet, 'I' . $r);
            $lainTarif = $this->readNumericCell($sheet, 'J' . $r);
            $lainGaji = round($lainQty * $lainTarif, 2);

            $tunjHadir = $this->readNumericCell($sheet, 'K' . $r);
            $tunjMakan = $this->readNumericCell($sheet, 'L' . $r);
            $tunjLain = $this->readNumericCell($sheet, 'M' . $r);
            $subTunj = round($tunjHadir + $tunjMakan + $tunjLain, 2);

            $pot = $this->readNumericCell($sheet, 'N' . $r);
            $ket = trim((string) $sheet->getCell('O' . $r)->getValue()) ?: null;

            $subBorongan = round($protolGaji + $packGaji + $lainGaji, 2);
            $totalItem = max(0.0, round($subBorongan + $subTunj - $pot, 2));

            if ($totalItem <= 0 && $subBorongan <= 0) {
                continue;
            }

            $totBorongan += $subBorongan;
            $totTunjangan += $subTunj;
            $totPotongan += $pot;

            $items[] = [
                'urutan'          => count($items) + 1,
                'nama_pekerja'    => $nama,
                'nik_pekerja'     => $nik,
                'tanggal_kerja'   => $tglTransaksi,
                'jenis_pekerjaan' => $jenis,
                'protol_qty'      => $protolQty,
                'protol_tarif'    => $protolTarif,
                'protol_gaji'     => $protolGaji,
                'packing_qty'     => $packQty,
                'packing_tarif'   => $packTarif,
                'packing_gaji'    => $packGaji,
                'lain_qty'        => $lainQty,
                'lain_tarif'      => $lainTarif,
                'lain_gaji'       => $lainGaji,
                'tunj_hadir'      => $tunjHadir,
                'tunj_makan'      => $tunjMakan,
                'tunj_lain'       => $tunjLain,
                'potongan'        => $pot,
                'total'           => $totalItem,
                'keterangan'      => $ket,
            ];
        }

        if (empty($items)) {
            throw new \RuntimeException('Tidak ada baris data pekerja borongan yang valid untuk diimpor.');
        }

        $grandTotal = max(0.0, round($totBorongan + $totTunjangan - $totPotongan, 2));

        DB::beginTransaction();
        try {
            $pb = PayrollBorongan::create([
                'kode_bukti'          => PayrollBorongan::generateKodeBukti(Carbon::parse($tglTransaksi)),
                'periode'             => $periode,
                'tanggal_transaksi'   => $tglTransaksi,
                'tanggal_mulai'       => Carbon::parse($tglTransaksi)->startOfWeek()->toDateString(),
                'tanggal_selesai'     => Carbon::parse($tglTransaksi)->endOfWeek()->toDateString(),
                'kode_gudang'         => $kodeGudang,
                'metode_pembayaran'   => 'Transfer Bank',
                'total_gaji_borongan' => $totBorongan,
                'total_tunjangan'     => $totTunjangan,
                'total_potongan'      => $totPotongan,
                'grand_total'         => $grandTotal,
                'status'              => $status,
                'post_to_ledger'      => false,
                'keterangan'          => "Impor Excel Borongan {$periode} (" . count($items) . " pekerja)",
                'created_by'          => auth()->id(),
            ]);

            foreach ($items as $item) {
                $item['payroll_borongan_id'] = $pb->id;
                PayrollBoronganItem::create($item);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'success'       => true,
            'borongan_id'   => $pb->id,
            'kode_bukti'    => $pb->kode_bukti,
            'periode'       => $periode,
            'total_pekerja' => count($items),
            'grand_total'   => $grandTotal,
        ];
    }

    /**
     * Generate Template Excel untuk Borongan.
     */
    public function generateBoronganTemplateSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Payroll Borongan');

        $sheet->setCellValue('A1', 'FORMAT IMPOR PAYROLL BORONGAN / FREELANCE PRODUKSI TTC');
        $sheet->setCellValue('A2', 'PERIODE: ' . now()->format('Y-m'));
        $sheet->mergeCells('A1:O1');
        $sheet->mergeCells('A2:O2');
        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(13);

        $headers = [
            'A4' => 'No',
            'B4' => 'Nama Pekerja',
            'C4' => 'NIK/KTP',
            'D4' => 'Jenis Pekerjaan',
            'E4' => 'Protol Qty',
            'F4' => 'Protol Tarif',
            'G4' => 'Packing Qty',
            'H4' => 'Packing Tarif',
            'I4' => 'Lain Qty',
            'J4' => 'Lain Tarif',
            'K4' => 'Tunj Hadir',
            'L4' => 'Tunj Makan',
            'M4' => 'Tunj Lain',
            'N4' => 'Potongan',
            'O4' => 'Keterangan',
        ];

        foreach ($headers as $c => $t) {
            $sheet->setCellValue($c, $t);
        }

        $style = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D97706']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A4:O4')->applyFromArray($style);

        // Contoh Data
        $sheet->setCellValue('A5', 1);
        $sheet->setCellValue('B5', 'Ahmad Pekerja');
        $sheet->setCellValue('C5', '3507110001');
        $sheet->setCellValue('D5', 'Protol & Finishing');
        $sheet->setCellValue('E5', 500);
        $sheet->setCellValue('F5', 500);
        $sheet->setCellValue('G5', 200);
        $sheet->setCellValue('H5', 1000);
        $sheet->setCellValue('I5', 0);
        $sheet->setCellValue('J5', 0);
        $sheet->setCellValue('K5', 25000);
        $sheet->setCellValue('L5', 15000);
        $sheet->setCellValue('M5', 0);
        $sheet->setCellValue('N5', 0);
        $sheet->setCellValue('O5', 'Borongan shift malam');

        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    protected function isHeaderRow(array $headers): bool
    {
        $joined = implode(' ', array_values($headers));
        return str_contains($joined, 'nama') && (str_contains($joined, 'gaji') || str_contains($joined, 'pokok') || str_contains($joined, 'upah'));
    }

    protected function mapColumns(array $headers): array
    {
        $map = [];
        foreach ($headers as $col => $rawHeader) {
            $header = preg_replace('/\s+/', ' ', strtolower(trim((string) $rawHeader))) ?? '';

            if ($header === '' || $header === 'no' || $header === 'no.') {
                continue;
            }

            // Skip kolom agregat total potongan / total pendapatan agar tidak menimpa komponen
            if (str_contains($header, 'total potongan') || str_contains($header, 'total pendapatan') || str_contains($header, 'jumlah potongan')) {
                continue;
            }

            $isPotongan = str_contains($header, 'pot.') || str_contains($header, 'potongan') || str_starts_with($header, 'pot ');
            $isTunjangan = str_contains($header, 'tunj') || preg_match('/^t\.\s*/', $header) === 1;

            // 1. Komponen POTONGAN diperiksa terlebih dahulu agar tidak tertukar dengan Tunjangan/Kehadiran
            if ($isPotongan) {
                if (str_contains($header, 'hadir') || str_contains($header, 'absen') || str_contains($header, 'alpha') || str_contains($header, 'telat') || str_contains($header, 'scorsing')) {
                    $map['potongan_kehadiran'] = $col;
                } elseif (str_contains($header, 'kasbon') || str_contains($header, 'pinjam')) {
                    $map['potongan_pinjaman'] = $col;
                } elseif (str_contains($header, 'bpjs') || str_contains($header, 'tenaga kerja') || str_contains($header, 'ketenagakerjaan')) {
                    $map['potongan_bpjs'] = $col;
                } elseif (str_contains($header, 'pph') || str_contains($header, 'pajak')) {
                    $map['potongan_pph21'] = $col;
                } elseif (str_contains($header, 'lain')) {
                    $map['potongan_lain'] = $col;
                }
                continue;
            }

            // 2. Komponen TUNJANGAN / T.* diperiksa sebelum 'jabatan' polos agar "T. Jabatan" tidak menimpa kolom "Jabatan"
            if ($isTunjangan) {
                if (str_contains($header, 'jabatan')) {
                    $map['tunjangan_jabatan'] = $col;
                } elseif (str_contains($header, 'kompetensi')) {
                    $map['tunjangan_kompetensi'] = $col;
                } elseif (str_contains($header, 'makan')) {
                    $map['tunjangan_makan'] = $col;
                } elseif (str_contains($header, 'hadir')) {
                    $map['tunjangan_hadir'] = $col;
                } elseif (str_contains($header, 'insentif') || str_contains($header, 'intensif')) {
                    $map['tunjangan_intensif'] = $col;
                } elseif (str_contains($header, 'lain')) {
                    $map['tunjangan_lain'] = $col;
                }
                continue;
            }

            // 3. Kolom identitas, gaji pokok, lembur, bonus, potongan non-prefiks, dan THP
            if (str_contains($header, 'kode') || str_contains($header, 'nik') || str_contains($header, 'nip')) {
                $map['kode'] = $col;
            } elseif (str_contains($header, 'nama')) {
                $map['nama'] = $col;
            } elseif (str_contains($header, 'jabatan') || str_contains($header, 'posisi')) {
                $map['jabatan'] = $col;
            } elseif (str_contains($header, 'thp') || str_contains($header, 'take home') || str_contains($header, 'gaji bersih') || str_contains($header, 'bersih')) {
                $map['gaji_bersih'] = $col;
            } elseif (str_contains($header, 'pokok') || $header === 'gaji') {
                $map['gaji_pokok'] = $col;
            } elseif (str_contains($header, 'insentif') || str_contains($header, 'intensif')) {
                $map['tunjangan_intensif'] = $col;
            } elseif (str_contains($header, 'lembur')) {
                $map['total_lembur'] = $col;
            } elseif (str_contains($header, 'bonus')) {
                $map['total_bonus'] = $col;
            } elseif (str_contains($header, 'absen') || str_contains($header, 'alpha') || str_contains($header, 'telat')) {
                $map['potongan_kehadiran'] = $col;
            } elseif (str_contains($header, 'kasbon') || str_contains($header, 'pinjam')) {
                $map['potongan_pinjaman'] = $col;
            } elseif (str_contains($header, 'bpjs') && (str_contains($header, 'pt') || str_contains($header, 'perusahaan') || str_contains($header, '3,7') || str_contains($header, '3.7'))) {
                $map['bpjs_perusahaan'] = $col;
            } elseif (str_contains($header, 'bpjs') || str_contains($header, 'tenaga kerja') || str_contains($header, 'ketenagakerjaan')) {
                $map['potongan_bpjs'] = $col;
            } elseif (str_contains($header, 'pph') || str_contains($header, 'pajak')) {
                $map['potongan_pph21'] = $col;
            }
        }

        return $map;
    }

    protected function parseIndonesianMonth(string $monthName): ?int
    {
        $months = [
            'januari'   => 1, 'jan' => 1,
            'februari'  => 2, 'feb' => 2,
            'maret'     => 3, 'mar' => 3,
            'april'     => 4, 'apr' => 4,
            'mei'       => 5, 'may' => 5,
            'juni'      => 6, 'jun' => 6,
            'juli'      => 7, 'jul' => 7,
            'agustus'   => 8, 'agu' => 8, 'agt' => 8, 'aug' => 8,
            'september' => 9, 'sep' => 9,
            'oktober'   => 10, 'okt' => 10, 'oct' => 10,
            'november'  => 11, 'nov' => 11,
            'desember'  => 12, 'des' => 12, 'dec' => 12,
        ];

        return $months[strtolower(trim($monthName))] ?? null;
    }
}
