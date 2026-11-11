<?php

namespace App\Filament\Admin\Resources\ReportFinaceResource\Pages;

use App\Filament\Admin\Resources\ReportFinaceResource;
use App\Models\CoaMovement;
use App\Models\MasterCOA;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\MaxWidth;

class ListReportFinaces extends Page
{
    protected static string $resource = ReportFinaceResource::class;
    protected static ?string $title = 'Report Finance — Trial Balance (Neraca Saldo)';
    protected static string $view = 'filament.admin.pages.trial-balance';

    public bool $onlyActiveAccounts = true;
    public string $selectedGroup = '';
    public string $searchQuery = '';

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function getTrialBalanceDataProperty(): array
    {
        $movementsByCoa = CoaMovement::query()
            ->selectRaw('coa_id, COALESCE(SUM(debit),0) as total_debit, COALESCE(SUM(kredit),0) as total_kredit, COUNT(*) as tx_count')
            ->groupBy('coa_id')
            ->get()
            ->keyBy('coa_id');

        $coas = MasterCOA::orderBy('kode_akun')->get();
        $rows = [];

        $grandOpenDebit = 0.0;
        $grandOpenKredit = 0.0;
        $grandMutDebit = 0.0;
        $grandMutKredit = 0.0;
        $grandEndDebit = 0.0;
        $grandEndKredit = 0.0;

        foreach ($coas as $coa) {
            $code = trim((string) $coa->kode_akun);
            $mov = $movementsByCoa->get($coa->id);

            $openDebit = round((float) ($coa->saldo_debit ?? 0), 2);
            $openKredit = round((float) ($coa->saldo_kredit ?? 0), 2);
            $mutDebit = round((float) ($mov->total_debit ?? 0), 2);
            $mutKredit = round((float) ($mov->total_kredit ?? 0), 2);
            $txCount = (int) ($mov->tx_count ?? 0);

            $hasActivity = ($openDebit > 0 || $openKredit > 0 || $mutDebit > 0 || $mutKredit > 0);
            if ($this->onlyActiveAccounts && ! $hasActivity) {
                continue;
            }

            if ($this->selectedGroup !== '' && ! str_starts_with($code, $this->selectedGroup)) {
                continue;
            }

            if (trim($this->searchQuery) !== '') {
                $q = mb_strtolower(trim($this->searchQuery));
                $hay = mb_strtolower($code . ' ' . ($coa->nama_akun ?? ''));
                if (! str_contains($hay, $q)) {
                    continue;
                }
            }

            $net = round(($openDebit + $mutDebit) - ($openKredit + $mutKredit), 2);
            $endDebit = $net > 0 ? $net : 0.0;
            $endKredit = $net < 0 ? abs($net) : 0.0;

            $firstDigit = substr($code, 0, 1);
            $groupMeta = match ($firstDigit) {
                '1' => ['label' => '1 - ASET / AKTIVA',      'bg' => '#dbeafe', 'fg' => '#1e40af', 'stmt' => 'NERACA'],
                '2' => ['label' => '2 - KEWAJIBAN / UTANG',  'bg' => '#fee2e2', 'fg' => '#b91c1c', 'stmt' => 'NERACA'],
                '3' => ['label' => '3 - EKUITAS / MODAL',    'bg' => '#f3e8ff', 'fg' => '#6b21a8', 'stmt' => 'NERACA'],
                '4' => ['label' => '4 - PENDAPATAN USAHA',   'bg' => '#dcfce7', 'fg' => '#15803d', 'stmt' => 'LABA RUGI'],
                '5' => ['label' => '5 - HARGA POKOK (HPP)',  'bg' => '#ffedd5', 'fg' => '#c2410c', 'stmt' => 'LABA RUGI'],
                '6' => ['label' => '6 - BEBAN OPERASIONAL',  'bg' => '#fef3c7', 'fg' => '#b45309', 'stmt' => 'LABA RUGI'],
                default => ['label' => 'LAINNYA',            'bg' => '#f1f5f9', 'fg' => '#475569', 'stmt' => 'LAINNYA'],
            };

            $rows[] = [
                'id'          => $coa->id,
                'kode_akun'   => $code,
                'nama_akun'   => $coa->nama_akun,
                'group_label' => $groupMeta['label'],
                'group_bg'    => $groupMeta['bg'],
                'group_fg'    => $groupMeta['fg'],
                'stmt'        => $groupMeta['stmt'],
                'tx_count'    => $txCount,
                'open_debit'  => $openDebit,
                'open_kredit' => $openKredit,
                'mut_debit'   => $mutDebit,
                'mut_kredit'  => $mutKredit,
                'end_debit'   => $endDebit,
                'end_kredit'  => $endKredit,
            ];

            $grandOpenDebit += $openDebit;
            $grandOpenKredit += $openKredit;
            $grandMutDebit += $mutDebit;
            $grandMutKredit += $mutKredit;
            $grandEndDebit += $endDebit;
            $grandEndKredit += $endKredit;
        }

        $selisihAkhir = round(abs($grandEndDebit - $grandEndKredit), 2);

        return [
            'rows'              => $rows,
            'grand_open_debit'  => round($grandOpenDebit, 2),
            'grand_open_kredit' => round($grandOpenKredit, 2),
            'grand_mut_debit'   => round($grandMutDebit, 2),
            'grand_mut_kredit'  => round($grandMutKredit, 2),
            'grand_end_debit'   => round($grandEndDebit, 2),
            'grand_end_kredit'  => round($grandEndKredit, 2),
            'selisih_akhir'     => $selisihAkhir,
            'is_balanced'       => $selisihAkhir < 1.0,
            'total_coa_db'      => $coas->count(),
            'shown_count'       => count($rows),
        ];
    }
}
