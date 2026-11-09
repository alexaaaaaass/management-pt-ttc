<?php

namespace App\Services\Marketing;

use App\Models\ApprovalForm;
use App\Models\ApprovalFormItem;
use App\Models\FinishGoodItem;
use App\Models\OrderConfirmation;
use App\Models\OrderConfirmationItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Support\Facades\DB;

class MarketingWorkflowService
{
    /**
     * Buat atau ambil Finish Good Item dari baris Approval Form Item
     * Meng-copy spesifikasi teknis (kertas, warna, jenis, komponen, finishing)
     * sehingga Operator/PPIC tinggal mengisi Bill of Materials (BOM)
     */
    public function createOrLinkFinishGoodFromApprovalFormItem(ApprovalFormItem $item): FinishGoodItem
    {
        return DB::transaction(function () use ($item) {
            if ($item->finish_good_item_id && $item->finishGoodItem) {
                return $item->finishGoodItem;
            }

            $af = $item->approvalForm;
            $customer = $af->customer;

            // Default Satuan dan Tipe Item
            $satuanId = \App\Models\MasterSatuan::where('nama_satuan', 'like', '%PCS%')
                ->orWhere('kode_satuan', 'like', '%PCS%')
                ->value('id') ?? (\App\Models\MasterSatuan::first()?->id ?? 1);

            $typeItemId = \App\Models\TypeItem::where('nama_type_item', 'like', '%ETIKET%')
                ->orWhere('nama_type_item', 'like', '%KEMASAN%')
                ->value('id') ?? (\App\Models\TypeItem::first()?->id ?? 1);

            $custCode = $customer ? ($customer->kode_customer ?: 'C' . str_pad($customer->id, 3, '0', STR_PAD_LEFT)) : 'CUST';
            $itemSlug = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $item->item_name), 0, 8));
            $uniqueSuffix = strtoupper(substr(uniqid(), -4));
            $kodeMaterial = "FG-{$custCode}-{$itemSlug}-{$uniqueSuffix}";

            $fg = FinishGoodItem::create([
                'customer_id'          => $af->customer_id,
                'nama_barang'          => $item->item_name,
                'kode_material_produk' => $kodeMaterial,
                'satuan_id'            => $satuanId,
                'type_item_id'         => $typeItemId,
                'jenis'                => $item->jenis,
                'komponen'             => $item->komponen,
                'isi'                  => $item->isi,
                'spesifikasi_kertas'   => $item->kertas,
                'warna'                => $item->warna,
                'finishing'            => $item->finishing,
                'deskripsi'            => "Dibuat otomatis dari Approval Form No: {$af->form_no}",
            ]);

            $item->updateQuietly([
                'finish_good_item_id' => $fg->id,
            ]);

            return $fg;
        });
    }
    /**
     * Konversi Approval Form menjadi Order Confirmation
     */
    public function generateOrderConfirmationFromApprovalForm(ApprovalForm $approvalForm): OrderConfirmation
    {
        return DB::transaction(function () use ($approvalForm) {
            $approvalForm->loadMissing(['customer', 'items']);

            // Jika sudah ada OC sebelumnya, gunakan yang sudah ada
            if ($approvalForm->orderConfirmation) {
                return $approvalForm->orderConfirmation;
            }

            $orderId = $approvalForm->order_id ?: ApprovalForm::generateOrderId($approvalForm->customer);
            $docNo = OrderConfirmation::generateDocNo($approvalForm->customer, $orderId);

            $orderConfirmation = OrderConfirmation::create([
                'approval_form_id'  => $approvalForm->id,
                'order_id'          => $orderId,
                'doc_no'            => $docNo,
                'customer_id'       => $approvalForm->customer_id,
                'tanggal'           => now(),
                'lead_time'         => $approvalForm->lead_time ?: '21 Hari Kerja',
                'subtotal'          => 0,
                'diskon'            => 0,
                'grand_total'       => 0,
                'terms_conditions'  => OrderConfirmation::defaultTermsConditions(),
                'status'            => 'draft',
                'disetujui_customer'=> $approvalForm->customer?->nama_customer ?: $approvalForm->customer?->nama_pic,
                'diketahui_ttc'     => 'PT. Teknografi Tri Cawanaska',
                'created_by'        => auth()->id(),
            ]);

            foreach ($approvalForm->items as $item) {
                OrderConfirmationItem::create([
                    'order_confirmation_id' => $orderConfirmation->id,
                    'approval_form_item_id' => $item->id,
                    'finish_good_item_id'   => $item->finish_good_item_id,
                    'item_name'             => $item->item_name,
                    'jenis'                 => $item->jenis,
                    'komponen'              => $item->komponen,
                    'isi'                   => $item->isi,
                    'kertas'                => $item->kertas,
                    'warna'                 => $item->warna,
                    'finishing'             => $item->finishing,
                    'qty'                   => $item->qty,
                    'harga'                 => 0, // Diisi oleh Marketing
                    'total'                 => 0,
                    'satuan'                => $item->satuan ?: 'Pcs',
                ]);
            }

            $approvalForm->update(['status' => 'converted_to_oc']);

            return $orderConfirmation;
        });
    }

    /**
     * Ambil rincian progres konversi item Order Confirmation ke Sales Order
     */
    public function getOrderConfirmationProgress(OrderConfirmation $oc): array
    {
        $oc->loadMissing(['items.finishGoodItem', 'salesOrders']);

        $totalItems = $oc->items->count();
        $existingSoFgIds = SalesOrder::where('order_confirmation_id', $oc->id)
            ->pluck('itemable_id')
            ->filter()
            ->toArray();

        $convertedCount = count(array_intersect(
            $oc->items->pluck('finish_good_item_id')->filter()->toArray(),
            $existingSoFgIds
        ));

        // Fallback jika FG ID belum terhubung langsung
        if ($convertedCount === 0 && $oc->salesOrders->count() > 0) {
            $convertedCount = min($oc->salesOrders->count(), $totalItems);
        }

        $unconvertedCount = max(0, $totalItems - $convertedCount);

        $status = 'belum_so';
        if ($convertedCount > 0 && $unconvertedCount > 0) {
            $status = 'partial_so';
        } elseif ($totalItems > 0 && $convertedCount >= $totalItems) {
            $status = 'converted_to_so';
        }

        return [
            'total_items'        => $totalItems,
            'converted_count'    => $convertedCount,
            'unconverted_count'  => $unconvertedCount,
            'status'             => $status,
            'existing_so_fg_ids' => $existingSoFgIds,
        ];
    }

    /**
     * Tentukan item pesanan Order Confirmation berikutnya yang perlu dibuatkan Sales Order
     */
    public function resolveTargetItemForSalesOrder(OrderConfirmation $oc, ?int $preferredItemId = null): ?OrderConfirmationItem
    {
        $oc->loadMissing(['items.finishGoodItem']);

        if ($preferredItemId) {
            $preferred = $oc->items->firstWhere('id', $preferredItemId);
            if ($preferred) {
                return $preferred;
            }
        }

        $existingSoFgIds = SalesOrder::where('order_confirmation_id', $oc->id)
            ->pluck('itemable_id')
            ->filter()
            ->toArray();

        return $oc->items->first(function ($it) use ($existingSoFgIds) {
            return $it->finish_good_item_id && ! in_array($it->finish_good_item_id, $existingSoFgIds);
        }) ?? $oc->items->first();
    }

    /**
     * Sinkronisasi status Order Confirmation berdasarkan progres Sales Order (1 SO = 1 SPK)
     */
    public function syncOrderConfirmationStatus(OrderConfirmation $oc, ?int $latestSalesOrderId = null): OrderConfirmation
    {
        $progress = $this->getOrderConfirmationProgress($oc);

        $updateData = [];
        if ($latestSalesOrderId) {
            $updateData['sales_order_id'] = $latestSalesOrderId;
        }

        if ($progress['unconverted_count'] > 0 && $progress['converted_count'] > 0) {
            $updateData['status'] = 'partial_so';
        } elseif ($progress['total_items'] > 0 && $progress['converted_count'] >= $progress['total_items']) {
            $updateData['status'] = 'converted_to_so';
        }

        if (! empty($updateData)) {
            $oc->update($updateData);
        }

        return $oc->fresh();
    }

    /**
     * Konversi Order Confirmation yang telah Deal menjadi Sales Order resmi (per item sesuai aturan 1 SO = 1 SPK)
     */
    public function convertOrderConfirmationToSalesOrder(OrderConfirmation $oc, ?int $targetItemId = null): SalesOrder
    {
        return DB::transaction(function () use ($oc, $targetItemId) {
            $oc->loadMissing(['customer', 'items.finishGoodItem']);

            $targetItem = $this->resolveTargetItemForSalesOrder($oc, $targetItemId);
            if (! $targetItem) {
                throw new \InvalidArgumentException("Order Confirmation {$oc->doc_no} tidak memiliki item penawaran.");
            }

            $fg = $targetItem->finishGoodItem;
            if (! $fg && $targetItem->item_name) {
                $fg = FinishGoodItem::where('nama_barang', $targetItem->item_name)->first();
            }

            $salesOrder = SalesOrder::create([
                'order_confirmation_id' => $oc->id,
                'no_sales_order'        => $oc->order_id ?: ('SO-' . date('y') . '-' . str_pad($oc->id, 4, '0', STR_PAD_LEFT)),
                'customer_id'           => $oc->customer_id,
                'tanggal_po'            => $oc->tanggal ?: now(),
                'lead_time'             => $oc->lead_time ?: '21 Hari Kerja',
                'order_type'            => 'SPK',
                'itemable_id'           => $fg?->id,
                'itemable_type'         => $fg ? FinishGoodItem::class : null,
                'product_name'          => $targetItem->item_name ?: $fg?->nama_barang,
                'qty'                   => (float) ($targetItem->qty ?? 0),
                'harga_pcs'             => (float) ($targetItem->harga ?? 0),
                'harga_kirim'           => 0,
                'diskon'                => (float) ($oc->diskon ?? 0),
                'jenis'                 => $targetItem->jenis ?: $fg?->jenis,
                'komponen'              => $targetItem->komponen ?: ($fg?->komponen ?? $fg?->typeItem?->nama_type_item),
                'isi'                   => $targetItem->isi ?: $fg?->isi,
                'kertas'                => $targetItem->kertas ?: $fg?->spesifikasi_kertas,
                'warna'                 => $targetItem->warna ?: $fg?->warna,
                'finishing'             => $targetItem->finishing ?: $fg?->finishing,
                'mata_uang'             => 'rupiah',
                'syarat_pembayaran'     => '30 Hari setelah invoice',
                'toleransi_pengiriman'  => 0,
                'klaim_kertas'          => 'not_claim',
                'catatan'               => $oc->catatan,
            ]);

            $this->syncOrderConfirmationStatus($oc, $salesOrder->id);

            return $salesOrder;
        });
    }
}
