<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

<style>
    /* ==========================================================================
       Accurate Online (AOL) Design System & Typography Engine
       ========================================================================== */
    :root {
        --font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    /* Universal Enterprise Font Enforcement (Accurate Online Inter) */
    html,
    body,
    button,
    input,
    optgroup,
    select,
    textarea,
    .fi-body,
    .fi-main,
    .fi-sidebar,
    .fi-topbar,
    .fi-header,
    .fi-ta,
    .fi-fo,
    .fi-in,
    .fi-modal,
    .fi-dropdown,
    .fi-tabs,
    .fi-breadcrumbs,
    .fi-badge {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    /* Financial & Tabular Numerals (Accurate Standard for numbers, currencies, dates, codes) */
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

    /* Accurate Card & Surface Refinement */
    .fi-section,
    .fi-ta-ctn,
    .fi-wi-stats-overview-stat {
        border-color: #e2e8f0 !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.04) !important;
        border-radius: 0.625rem !important;
    }

    /* Accurate Table Headers: crisp, uppercase, high readability */
    .fi-ta-header-cell {
        font-size: 0.725rem !important;
        letter-spacing: 0.04em !important;
        text-transform: uppercase !important;
        font-weight: 600 !important;
        color: #475569 !important;
    }

    /* Accurate Metric Card Accents */
    .fi-stats-overview-stat-value {
        font-weight: 700 !important;
        letter-spacing: -0.02em !important;
    }

    /* Sidebar Active & Navigation Accents (Accurate Corporate Blue) */
    .fi-sidebar-item-active > a {
        font-weight: 600 !important;
    }
</style>
