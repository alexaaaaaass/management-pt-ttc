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

<style>
    .ttc-finance-tabs-nav {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 14px;
        padding: 10px 14px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
    }
    .dark .ttc-finance-tabs-nav {
        background: #111827;
        border-color: #1f2937;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    }
    .ttc-tab-pill-badge {
        background: #0f172a;
        color: #f8fafc;
        font-size: 10.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 6px 12px;
        border-radius: 8px;
        white-space: nowrap;
    }
    .dark .ttc-tab-pill-badge {
        background: #1e293b;
        color: #93c5fd;
        border: 1px solid #334155;
    }
    .ttc-tab-item {
        display: inline-flex;
        flex-direction: column;
        padding: 8px 15px;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.15s ease;
        white-space: nowrap;
    }
    .ttc-tab-item-inactive {
        background: #f8fafc;
        color: #334155;
        border: 1px solid #e2e8f0;
    }
    .dark .ttc-tab-item-inactive {
        background: #1e293b;
        color: #e2e8f0;
        border-color: #334155;
    }
    .dark .ttc-tab-item-inactive:hover {
        background: #334155;
        color: #ffffff;
        border-color: #475569;
    }
    .ttc-tab-item-inactive:hover {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }
    .ttc-tab-sub-inactive {
        color: #64748b;
    }
    .dark .ttc-tab-sub-inactive {
        color: #94a3b8;
    }
</style>

<div class="ttc-finance-tabs-nav">
    <div style="display: flex; align-items: center; gap: 8px; padding-right: 8px;">
        <span class="ttc-tab-pill-badge">
            Sub-Menu Report Finance
        </span>
    </div>
    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 8px; flex: 1;">
        @foreach($tabs as $tab)
            <a href="{{ $tab['url'] }}"
               class="ttc-tab-item {{ $tab['active'] ? '' : 'ttc-tab-item-inactive' }}"
               style="{{ $tab['active'] ? 'background: ' . $tab['color'] . '; color: #ffffff; box-shadow: 0 2px 6px rgba(15,23,42,0.16);' : '' }}">
                <span style="font-size: 12.5px; font-weight: 800; line-height: 1.2;">
                    {{ $tab['label'] }}
                </span>
                <span class="{{ $tab['active'] ? '' : 'ttc-tab-sub-inactive' }}" style="font-size: 10px; font-weight: 600; margin-top: 2px; {{ $tab['active'] ? 'color: rgba(255,255,255,0.85);' : '' }}">
                    {{ $tab['sub'] }}
                </span>
            </a>
        @endforeach
    </div>
</div>

