@php
    $oc = $orderConfirmation;
    $items = $oc->items;
    $customer = $oc->customer;

    $statusLabel = match ($oc->status) {
        'draft'           => 'DRAFT',
        'reviewed'        => 'REVIEWED MARKETING',
        'approved'        => 'APPROVED',
        'deal'            => 'DEAL CUSTOMER',
        'partial_so'      => 'SEBAGIAN SO TERBIT',
        'converted_to_so' => 'SEMUA SO TERBIT',
        'rejected'        => 'DITOLAK',
        default           => strtoupper($oc->status ?? '-'),
    };

    $isSuccessStatus = in_array($oc->status, ['deal', 'converted_to_so']);
    $isDangerStatus = $oc->status === 'rejected';
@endphp

<style>
    .oc-modal-wrap {
        --oc-bg-card: #ffffff;
        --oc-bg-subtle: #f8fafc;
        --oc-bg-badge: #f1f5f9;
        --oc-border: #e2e8f0;
        --oc-border-subtle: #e2e8f0;
        --oc-text-main: #0f172a;
        --oc-text-secondary: #475569;
        --oc-text-muted: #64748b;
        --oc-accent: #d97706;
        --oc-accent-bg: rgba(245, 158, 11, 0.08);
        --oc-accent-border: rgba(245, 158, 11, 0.35);
        --oc-success: #059669;
        --oc-success-bg: rgba(16, 185, 129, 0.08);
        --oc-success-border: rgba(16, 185, 129, 0.3);
        --oc-danger: #e11d48;
        --oc-danger-bg: rgba(225, 29, 72, 0.08);
        --oc-danger-border: rgba(225, 29, 72, 0.3);
        color: var(--oc-text-main);
        font-family: inherit;
    }

    .dark .oc-modal-wrap {
        --oc-bg-card: #18181b;
        --oc-bg-subtle: #222226;
        --oc-bg-badge: #27272a;
        --oc-border: #2e2e34;
        --oc-border-subtle: #323238;
        --oc-text-main: #f4f4f5;
        --oc-text-secondary: #d4d4d8;
        --oc-text-muted: #a1a1aa;
        --oc-accent: #fbbf24;
        --oc-accent-bg: rgba(245, 158, 11, 0.12);
        --oc-accent-border: rgba(245, 158, 11, 0.4);
        --oc-success: #34d399;
        --oc-success-bg: rgba(16, 185, 129, 0.12);
        --oc-success-border: rgba(16, 185, 129, 0.35);
        --oc-danger: #fb7185;
        --oc-danger-bg: rgba(225, 29, 72, 0.12);
        --oc-danger-border: rgba(225, 29, 72, 0.35);
    }

    .oc-card {
        background-color: var(--oc-bg-card);
        border: 1px solid var(--oc-border);
        border-radius: 10px;
        padding: 16px 20px;
    }

    .oc-card-active {
        border-color: var(--oc-accent-border);
    }

    .oc-pill {
        display: inline-flex;
        align-items: center;
        padding: 2px 9px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.02em;
        background-color: var(--oc-bg-badge);
        color: var(--oc-text-secondary);
        border: 1px solid var(--oc-border-subtle);
    }

    .oc-pill-success {
        background-color: var(--oc-success-bg);
        color: var(--oc-success);
        border-color: var(--oc-success-border);
    }

    .oc-pill-accent {
        background-color: var(--oc-accent-bg);
        color: var(--oc-accent);
        border-color: var(--oc-accent-border);
    }

    .oc-pill-danger {
        background-color: var(--oc-danger-bg);
        color: var(--oc-danger);
        border-color: var(--oc-danger-border);
    }

    .oc-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 500;
        border-radius: 6px;
        text-decoration: none;
        transition: opacity 0.15s ease;
        border: 1px solid var(--oc-border);
        background-color: var(--oc-bg-subtle);
        color: var(--oc-text-main);
    }

    .oc-btn:hover {
        opacity: 0.85;
    }

    .oc-btn-primary {
        background-color: #d97706;
        border-color: #d97706;
        color: #ffffff;
    }

    .oc-spec-box {
        background-color: var(--oc-bg-subtle);
        border: 1px solid var(--oc-border-subtle);
        border-radius: 6px;
        padding: 8px 10px;
    }

    .oc-flexo-bar {
        background-color: var(--oc-bg-subtle);
        border: 1px solid var(--oc-border-subtle);
        border-left: 3px solid var(--oc-accent);
        border-radius: 6px;
        padding: 9px 14px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px 20px;
        font-size: 12px;
        color: var(--oc-text-secondary);
    }

    .oc-section-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--oc-text-muted);
    }
</style>

<div class="oc-modal-wrap space-y-5">

    {{-- ================= Header Info & Quick Actions ================= --}}
    <div class="oc-card">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="oc-section-title" style="color: var(--oc-accent);">Order Confirmation</span>
                    <span class="oc-pill {{ $isSuccessStatus ? 'oc-pill-success' : ($isDangerStatus ? 'oc-pill-danger' : 'oc-pill-accent') }}">
                        {{ $statusLabel }}
                    </span>
                    @if($salesOrder)
                        <span class="oc-pill">
                            SO: {{ $salesOrder->no_sales_order }}
                        </span>
                    @endif
                </div>

                <div class="flex flex-wrap items-baseline gap-2.5 pt-0.5">
                    <h2 style="font-size: 19px; font-weight: 700; color: var(--oc-text-main); margin: 0;">
                        {{ $oc->doc_no }}
                    </h2>
                    @if($oc->order_id)
                        <span class="oc-pill" style="font-family: ui-monospace, monospace; font-weight: 500;">
                            Order ID: {{ $oc->order_id }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <a
                    href="{{ route('filament.admin.resources.order-confirmations.edit', $oc) }}"
                    target="_blank"
                    class="oc-btn oc-btn-primary"
                >
                    Buka Dokumen OC
                </a>
                <a
                    href="{{ route('order-confirmation.preview', $oc) }}"
                    target="_blank"
                    class="oc-btn"
                >
                    Preview PDF
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4 pt-3.5" style="border-top: 1px solid var(--oc-border); font-size: 12px;">
            <div>
                <span style="display: block; color: var(--oc-text-muted); font-size: 11px;">Customer</span>
                <span style="font-weight: 600; color: var(--oc-text-main);">{{ $customer?->nama_customer ?? '-' }}</span>
            </div>
            <div>
                <span style="display: block; color: var(--oc-text-muted); font-size: 11px;">Tanggal OC</span>
                <span style="font-weight: 600; color: var(--oc-text-main);">{{ $oc->tanggal ? \Carbon\Carbon::parse($oc->tanggal)->format('d/m/Y') : '-' }}</span>
            </div>
            <div>
                <span style="display: block; color: var(--oc-text-muted); font-size: 11px;">Lead Time</span>
                <span style="font-weight: 600; color: var(--oc-text-main);">{{ $oc->lead_time ?: '21 Hari Kerja' }}</span>
            </div>
            <div>
                <span style="display: block; color: var(--oc-text-muted); font-size: 11px;">Total Item</span>
                <span style="font-weight: 600; color: var(--oc-text-main);">{{ $items->count() }} Item Pesanan</span>
            </div>
        </div>
    </div>

    {{-- ================= Daftar Item Order Confirmation ================= --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between px-0.5">
            <h3 class="oc-section-title">Daftar Item & Penawaran Harga</h3>
            <span style="font-size: 11px; color: var(--oc-text-muted);">
                {{ $items->count() }} Item
            </span>
        </div>

        @if($items->isEmpty())
            <div class="oc-card text-center" style="color: var(--oc-text-muted); font-size: 13px; padding: 32px;">
                Tidak ada item terdaftar pada Order Confirmation ini.
            </div>
        @else
            <div class="space-y-3">
                @foreach($items as $index => $item)
                    @php
                        $isCurrentSoItem = $salesOrder && (
                            ($item->finish_good_item_id && (int) $item->finish_good_item_id === (int) $salesOrder->itemable_id)
                            || trim(strtolower($item->item_name ?? '')) === trim(strtolower($salesOrder->product_name ?? ''))
                        );

                        $qty = (float) ($item->qty ?? 0);
                        $harga = (float) ($item->harga ?? 0);
                        $total = (float) ($item->total ?: ($qty * $harga));
                        $hpp = (float) ($item->hpp_per_pcs ?? 0);
                        $hargaIndikatif = (float) ($item->harga_jual_indikatif ?? 0);
                        $kertasKg = (float) ($item->kebutuhan_kertas_kg ?? 0);
                        $kertasMeter = (float) ($item->kebutuhan_kertas_meter ?? 0);
                    @endphp

                    <div class="oc-card {{ $isCurrentSoItem ? 'oc-card-active' : '' }}">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span style="font-size: 12px; font-weight: 600; color: var(--oc-text-muted);">
                                        {{ $index + 1 }}.
                                    </span>
                                    <h4 style="font-size: 15px; font-weight: 700; color: var(--oc-text-main); margin: 0;">
                                        {{ $item->item_name ?? ($item->finishGoodItem?->nama_barang ?? 'Item Penawaran') }}
                                    </h4>
                                    @if($isCurrentSoItem)
                                        <span class="oc-pill oc-pill-success">
                                            Terkait Sales Order Ini
                                        </span>
                                    @endif
                                </div>

                                @if($item->finishGoodItem)
                                    <div style="font-size: 11px; color: var(--oc-text-muted);" class="flex flex-wrap items-center gap-1.5">
                                        <span>Finish Good:</span>
                                        <span style="font-family: ui-monospace, monospace; color: var(--oc-text-secondary); font-weight: 600;">
                                            {{ $item->finishGoodItem->kode_material_produk ?? '-' }}
                                        </span>
                                        <span>({{ $item->finishGoodItem->nama_barang }})</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Harga & Total item --}}
                            <div class="sm:text-right shrink-0">
                                <div style="font-size: 11px; color: var(--oc-text-muted);">
                                    {{ number_format($qty, 0, ',', '.') }} {{ $item->satuan ?: 'Pcs' }} &times; Rp {{ number_format($harga, 2, ',', '.') }}
                                </div>
                                <div style="font-size: 15px; font-weight: 700; color: var(--oc-text-main); margin-top: 1px;">
                                    Rp {{ number_format($total, 2, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        {{-- Spesifikasi Teknis --}}
                        <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2">
                            <div class="oc-spec-box">
                                <span style="display: block; font-size: 10px; text-transform: uppercase; font-weight: 600; color: var(--oc-text-muted);">Jenis</span>
                                <span style="display: block; font-size: 12px; font-weight: 500; color: var(--oc-text-main); margin-top: 2px;" class="truncate">{{ $item->jenis ?: '-' }}</span>
                            </div>
                            <div class="oc-spec-box">
                                <span style="display: block; font-size: 10px; text-transform: uppercase; font-weight: 600; color: var(--oc-text-muted);">Komponen</span>
                                <span style="display: block; font-size: 12px; font-weight: 500; color: var(--oc-text-main); margin-top: 2px;" class="truncate">{{ $item->komponen ?: '-' }}</span>
                            </div>
                            <div class="oc-spec-box">
                                <span style="display: block; font-size: 10px; text-transform: uppercase; font-weight: 600; color: var(--oc-text-muted);">Kertas</span>
                                <span style="display: block; font-size: 12px; font-weight: 500; color: var(--oc-text-main); margin-top: 2px;" class="truncate">{{ $item->kertas ?: '-' }}</span>
                            </div>
                            <div class="oc-spec-box">
                                <span style="display: block; font-size: 10px; text-transform: uppercase; font-weight: 600; color: var(--oc-text-muted);">Warna</span>
                                <span style="display: block; font-size: 12px; font-weight: 500; color: var(--oc-text-main); margin-top: 2px;" class="truncate">{{ $item->warna ?: '-' }}</span>
                            </div>
                            <div class="oc-spec-box">
                                <span style="display: block; font-size: 10px; text-transform: uppercase; font-weight: 600; color: var(--oc-text-muted);">Finishing</span>
                                <span style="display: block; font-size: 12px; font-weight: 500; color: var(--oc-text-main); margin-top: 2px;" class="truncate" title="{{ $item->finishing }}">{{ $item->finishing ?: '-' }}</span>
                            </div>
                            <div class="oc-spec-box">
                                <span style="display: block; font-size: 10px; text-transform: uppercase; font-weight: 600; color: var(--oc-text-muted);">Isi / Batang</span>
                                <span style="display: block; font-size: 12px; font-weight: 500; color: var(--oc-text-main); margin-top: 2px;" class="truncate">{{ $item->isi ? $item->isi . ' Batang' : '-' }}</span>
                            </div>
                        </div>

                        {{-- Flexo HPP breakdown (Minimalis & Jelas di Dark/Light Mode) --}}
                        @if($hpp > 0 || $kertasKg > 0)
                            <div class="mt-3 oc-flexo-bar">
                                <span style="font-weight: 700; color: var(--oc-accent);">
                                    Kalkulasi Mesin Flexo
                                </span>
                                @if($kertasKg > 0)
                                    <span>
                                        Kebutuhan Kertas: <strong style="color: var(--oc-text-main); font-weight: 600;">{{ number_format($kertasKg, 2, ',', '.') }} kg ({{ number_format($kertasMeter, 0, ',', '.') }} m)</strong>
                                    </span>
                                @endif
                                @if($hpp > 0)
                                    <span>
                                        HPP Pokok: <strong style="color: var(--oc-text-main); font-weight: 600;">Rp {{ number_format($hpp, 2, ',', '.') }} / pcs</strong>
                                    </span>
                                @endif
                                @if($hargaIndikatif > 0)
                                    <span>
                                        Rekomendasi Jual: <strong style="color: var(--oc-text-main); font-weight: 600;">Rp {{ number_format($hargaIndikatif, 2, ',', '.') }} / pcs</strong>
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ================= Ringkasan Biaya ================= --}}
    <div class="oc-card">
        <h3 class="oc-section-title mb-3">Ringkasan Biaya Order Confirmation</h3>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="oc-spec-box" style="padding: 10px 14px;">
                <span style="display: block; font-size: 11px; color: var(--oc-text-muted);">Subtotal Penawaran</span>
                <span style="display: block; font-size: 15px; font-weight: 700; color: var(--oc-text-main); margin-top: 2px;">
                    Rp {{ number_format((float) ($oc->subtotal ?? 0), 2, ',', '.') }}
                </span>
            </div>
            <div class="oc-spec-box" style="padding: 10px 14px;">
                <span style="display: block; font-size: 11px; color: var(--oc-text-muted);">Diskon</span>
                <span style="display: block; font-size: 15px; font-weight: 700; color: var(--oc-accent); margin-top: 2px;">
                    Rp {{ number_format((float) ($oc->diskon ?? 0), 2, ',', '.') }}
                </span>
            </div>
            <div class="oc-spec-box" style="padding: 10px 14px; background-color: var(--oc-success-bg); border-color: var(--oc-success-border);">
                <span style="display: block; font-size: 11px; color: var(--oc-success); font-weight: 600;">Total Akhir (Grand Total)</span>
                <span style="display: block; font-size: 16px; font-weight: 800; color: var(--oc-success); margin-top: 2px;">
                    Rp {{ number_format((float) ($oc->grand_total ?? 0), 2, ',', '.') }}
                </span>
            </div>
        </div>

        @if(!empty($oc->catatan))
            <div class="mt-3 pt-3" style="border-top: 1px solid var(--oc-border); font-size: 12px;">
                <span style="font-weight: 600; color: var(--oc-text-secondary); display: block; margin-bottom: 2px;">Catatan Tambahan:</span>
                <p style="color: var(--oc-text-muted); margin: 0;" class="whitespace-pre-line">{{ $oc->catatan }}</p>
            </div>
        @endif
    </div>

</div>
