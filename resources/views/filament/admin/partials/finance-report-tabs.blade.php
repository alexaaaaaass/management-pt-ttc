@php
    $currentPath = request()->path();
    $tabs = [
        [
            'label'  => 'Laba Rugi (Global)',
            'sub'    => 'Income Statement',
            'url'    => url('/admin/laporan-laba-rugi'),
            'active' => str_contains($currentPath, 'laporan-laba-rugi') && !str_contains($currentPath, 'mesin'),
            'color'  => '#2563eb',
        ],
        [
            'label'  => 'Laba Rugi (Mesin)',
            'sub'    => 'Analisa per Mesin Cetak',
            'url'    => url('/admin/laporan-laba-rugi-mesin'),
            'active' => str_contains($currentPath, 'laporan-laba-rugi-mesin'),
            'color'  => '#7c3aed',
        ],
        [
            'label'  => 'Laporan Neraca',
            'sub'    => 'Aktiva & Pasiva',
            'url'    => url('/admin/laporan-neraca'),
            'active' => str_contains($currentPath, 'laporan-neraca'),
            'color'  => '#059669',
        ],
        [
            'label'  => 'Trial Balance (Summary COA)',
            'sub'    => 'Neraca Saldo 255 Akun',
            'url'    => url('/admin/report-finaces'),
            'active' => str_contains($currentPath, 'report-finaces'),
            'color'  => '#d97706',
        ],
        [
            'label'  => 'General Ledger',
            'sub'    => 'Buku Besar & Jurnal',
            'url'    => url('/admin/coa-ledgers'),
            'active' => str_contains($currentPath, 'coa-ledgers'),
            'color'  => '#0f172a',
        ],
    ];
@endphp

<div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 14px; padding: 10px 14px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);">
    <div style="display: flex; align-items: center; gap: 8px; padding-right: 8px;">
        <span style="background: #0f172a; color: #f8fafc; font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; padding: 5px 10px; border-radius: 8px; white-space: nowrap;">
            Sub-Menu Report Finance
        </span>
    </div>
    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px; flex: 1;">
        @foreach($tabs as $tab)
            <a href="{{ $tab['url'] }}"
               style="display: inline-flex; flex-direction: column; padding: 8px 15px; border-radius: 10px; text-decoration: none; transition: all 0.15s ease; white-space: nowrap; {{ $tab['active'] ? 'background: ' . $tab['color'] . '; color: #ffffff; box-shadow: 0 2px 6px rgba(15,23,42,0.16);' : 'background: #f8fafc; color: #334155; border: 1px solid #e2e8f0;' }}">
                <span style="font-size: 12.5px; font-weight: 800; line-height: 1.2;">
                    {{ $tab['label'] }}
                </span>
                <span style="font-size: 10px; font-weight: 600; margin-top: 2px; {{ $tab['active'] ? 'color: rgba(255,255,255,0.85);' : 'color: #64748b;' }}">
                    {{ $tab['sub'] }}
                </span>
            </a>
        @endforeach
    </div>
</div>

