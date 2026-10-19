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

            $docNo = OrderConfirmation::generateDocNo($approvalForm->customer);

            $orderConfirmation = OrderConfirmation::create([
                'approval_form_id'  => $approvalForm->id,
                'order_id'          => $approvalForm->order_id ?: ApprovalForm::generateOrderId($approvalForm->customer),
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
     * Konversi Order Confirmation yang telah Deal menjadi Sales Order resmi
     */
    public function convertOrderConfirmationToSalesOrder(OrderConfirmation $oc): SalesOrder
    {
        return DB::transaction(function () use ($oc) {
            $oc->loadMissing(['customer', 'items']);

            if ($oc->sales_order_id && $oc->salesOrder) {
                return $oc->salesOrder;
            }

            $firstItem = $oc->items->first();

            $salesOrder = SalesOrder::create([
                'no_sales_order'        => $oc->order_id ?: ('SO-' . date('y') . '-' . str_pad($oc->id, 4, '0', STR_PAD_LEFT)),
                'customer_id'           => $oc->customer_id,
                'tanggal_po'            => $oc->tanggal,
                'lead_time'             => $oc->lead_time,
                'itemable_id'           => $firstItem?->finish_good_item_id,
                'itemable_type'         => $firstItem?->finish_good_item_id ? \App\Models\FinishGoodItem::class : null,
                'qty'                   => $oc->items->sum('qty'),
                'harga_pcs'             => $firstItem?->harga ?? 0,
                'harga_kirim'           => 0,
                'diskon'                => $oc->diskon ?? 0,
                'jenis'                 => $firstItem?->jenis,
                'komponen'              => $firstItem?->komponen,
                'isi'                   => $firstItem?->isi,
                'kertas'                => $firstItem?->kertas,
                'warna'                 => $firstItem?->warna,
                'finishing'             => $firstItem?->finishing,
            ]);

            // Buat Sales Order Items jika ada multiple item
            foreach ($oc->items as $ocItem) {
                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'qty'            => $ocItem->qty,
                    'satuan'         => $ocItem->satuan ?: 'Pcs',
                    'harga'          => $ocItem->harga,
                    'total'          => $ocItem->total,
                ]);
            }

            $oc->update([
                'sales_order_id' => $salesOrder->id,
                'status'         => 'deal',
            ]);

            return $salesOrder;
        });
    }
}
