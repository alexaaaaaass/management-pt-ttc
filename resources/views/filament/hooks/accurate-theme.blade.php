<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

<style>
    /* ==========================================================================
       Accurate Online (AOL) Design System & Global Typography Engine
       Berlaku untuk SEMUA Menu, Sidebar, Header, Form, Tabel, Modal, & Widget
       ========================================================================== */
    :root {
        --font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
    }

    /* 1. Global Universal Selector (Memastikan 100% Elemen di Semua Menu Menggunakan Inter) */
    *, *::before, *::after {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
    }

    /* 2. Spesifik Komponen Filament di Seluruh Modul & Menu */
    html, body,
    .fi-body,
    .fi-layout,
    .fi-main,
    .fi-main-ctn,
    .fi-sidebar,
    .fi-sidebar-nav,
    .fi-sidebar-group,
    .fi-sidebar-group-label,
    .fi-sidebar-item,
    .fi-sidebar-item-button,
    .fi-sidebar-item-label,
    .fi-topbar,
    .fi-topbar-item,
    .fi-header,
    .fi-header-heading,
    .fi-header-subheading,
    .fi-breadcrumbs,
    .fi-breadcrumbs-item-label,
    .fi-page,
    .fi-section,
    .fi-section-header-heading,
    .fi-section-header-description,
    .fi-ta,
    .fi-ta-header,
    .fi-ta-header-cell,
    .fi-ta-cell,
    .fi-ta-text-item-label,
    .fi-ta-record,
    .fi-fo,
    .fi-fo-field-wrp-label,
    .fi-fo-field-wrp-helper-text,
    .fi-in,
    .fi-in-entry,
    .fi-modal,
    .fi-modal-heading,
    .fi-modal-description,
    .fi-dropdown,
    .fi-dropdown-list-item-label,
    .fi-tabs,
    .fi-tabs-item-label,
    .fi-badge,
    button, input, select, textarea, optgroup {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    /* 3. Tabular Numerals (Angka Finansial Tegak Lurus Khas Accurate) */
    .fi-ta-cell,
    .fi-ta-cell-numeric,
    .fi-in-entry,
    input[inputmode="decimal"],
    input[inputmode="numeric"],
    .fi-badge,
    .fi-stats-overview-stat-value,
    .tabular-nums {
        font-variant-numeric: tabular-nums !important;
        font-feature-settings: "tnum" 1, "cv02" 1, "cv03" 1, "cv04" 1 !important;
    }

    /* 4. Menu Sidebar (Accurate High-Density Menu Styling) */
    .fi-sidebar-group-label {
        font-size: 0.72rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.05em !important;
        text-transform: uppercase !important;
        color: #64748b !important;
    }

    .fi-sidebar-item-label {
        font-size: 0.865rem !important;
        font-weight: 500 !important;
    }

    .fi-sidebar-item-active > a .fi-sidebar-item-label {
        font-weight: 600 !important;
        color: #2563eb !important;
    }

    /* 5. Header Tabel & Form Label */
    .fi-ta-header-cell {
        font-size: 0.725rem !important;
        letter-spacing: 0.04em !important;
        text-transform: uppercase !important;
        font-weight: 600 !important;
        color: #475569 !important;
    }

    .fi-fo-field-wrp-label {
        font-size: 0.84rem !important;
        font-weight: 600 !important;
        color: #334155 !important;
    }

    /* 6. Accurate Card & Surface Refinement */
    .fi-section,
    .fi-ta-ctn,
    .fi-wi-stats-overview-stat,
    .fi-wi-chart {
        border-color: #e2e8f0 !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.04) !important;
        border-radius: 0.75rem !important;
    }

    /* 7. Hover Effect pada Stat Cards */
    .fi-wi-stats-overview-stat {
        transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
    }
    .fi-wi-stats-overview-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.07), 0 4px 6px -2px rgba(0, 0, 0, 0.04) !important;
    }

    /* 8. Canvas Grafik Arus Kas */
    .fi-wi-chart canvas {
        filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.02));
    }
</style>
