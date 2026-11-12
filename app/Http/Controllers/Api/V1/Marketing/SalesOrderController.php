<?php

namespace App\Http\Controllers\Api\V1\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\FinishGoodItem;
use App\Models\SalesOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = SalesOrder::with(['customer', 'itemable', 'orderConfirmation']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('no_sales_order', 'like', "%{$search}%")
                  ->orWhere('no_po_customer', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($cq) => $cq->where('nama_customer', 'like', "%{$search}%"));
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        $data = $query->latest('id')->paginate($perPage);

        $rows = collect($data->items())->map(function (SalesOrder $so) {
            return array_merge($so->toArray(), [
                'effective_product_name' => $so->product_name,
                'qty_delivered'          => (float) $so->qty_delivered,
                'qty_outstanding'        => (float) $so->qty_outstanding,
                'total_nilai'            => (float) $so->qty * (float) $so->harga_pcs,
            ]);
        });

        return response()->json([
            'data' => $rows,
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
            ],
        ]);
    }

    public function options(): JsonResponse
    {
        return response()->json([
            'customers' => Customer::select('id', 'kode_customer', 'nama_customer')
                ->orderBy('nama_customer')
                ->get(),
            'finish_goods' => FinishGoodItem::select(
                'id', 'customer_id', 'kode_material_produk', 'nama_barang',
                'jenis', 'komponen', 'isi', 'spesifikasi_kertas', 'warna', 'finishing'
            )->orderBy('nama_barang')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id'          => ['required', 'exists:customers,id'],
            'itemable_id'          => ['required', 'exists:finish_good_items,id'],
            'no_sales_order'       => ['nullable', 'string', 'max:100', 'unique:sales_orders,no_sales_order'],
            'no_po_customer'       => ['nullable', 'string', 'max:100'],
            'tanggal_po'           => ['required', 'date'],
            'lead_time'            => ['nullable', 'string', 'max:100'],
            'qty'                  => ['required', 'numeric', 'min:1'],
            'harga_pcs'            => ['required', 'numeric', 'min:0'],
            'diskon'               => ['nullable', 'numeric', 'min:0'],
            'syarat_pembayaran'    => ['nullable', 'string', 'max:255'],
            'toleransi_pengiriman' => ['nullable', 'numeric', 'min:0'],
            'catatan'              => ['nullable', 'string'],
        ]);

        $fg = FinishGoodItem::findOrFail($validated['itemable_id']);
        $noSo = $validated['no_sales_order'] ?: ('SO-' . date('ym') . '-' . str_pad((SalesOrder::withTrashed()->count() + 1), 4, '0', STR_PAD_LEFT));

        $so = SalesOrder::create([
            'customer_id'          => $validated['customer_id'],
            'itemable_type'        => FinishGoodItem::class,
            'itemable_id'          => $fg->id,
            'kode_material'        => $fg->kode_material_produk,
            'product_name'         => $fg->nama_barang,
            'no_sales_order'       => $noSo,
            'no_po_customer'       => $validated['no_po_customer'] ?? null,
            'tanggal_po'           => $validated['tanggal_po'],
            'lead_time'            => $validated['lead_time'] ?? '21 Hari Kerja',
            'order_type'           => 'SPK',
            'qty'                  => (float) $validated['qty'],
            'harga_pcs'            => (float) $validated['harga_pcs'],
            'harga_kirim'          => 0,
            'diskon'               => (float) ($validated['diskon'] ?? 0),
            'mata_uang'            => 'rupiah',
            'syarat_pembayaran'    => $validated['syarat_pembayaran'] ?? '30 Hari setelah invoice',
            'toleransi_pengiriman' => (float) ($validated['toleransi_pengiriman'] ?? 0),
            'klaim_kertas'         => 'not_claim',
            'jenis'                => $fg->jenis,
            'komponen'             => $fg->komponen,
            'isi'                  => $fg->isi,
            'kertas'               => $fg->spesifikasi_kertas,
            'warna'                => $fg->warna,
            'finishing'            => $fg->finishing,
            'catatan'              => $validated['catatan'] ?? null,
        ]);

        return response()->json([
            'message' => "Sales Order {$so->no_sales_order} berhasil dibuat.",
            'data'    => $so->load(['customer', 'itemable']),
        ], 201);
    }

    public function update(Request $request, SalesOrder $salesOrder): JsonResponse
    {
        $validated = $request->validate([
            'customer_id'          => ['required', 'exists:customers,id'],
            'itemable_id'          => ['required', 'exists:finish_good_items,id'],
            'no_po_customer'       => ['nullable', 'string', 'max:100'],
            'tanggal_po'           => ['required', 'date'],
            'lead_time'            => ['nullable', 'string', 'max:100'],
            'qty'                  => ['required', 'numeric', 'min:1'],
            'harga_pcs'            => ['required', 'numeric', 'min:0'],
            'diskon'               => ['nullable', 'numeric', 'min:0'],
            'syarat_pembayaran'    => ['nullable', 'string', 'max:255'],
            'toleransi_pengiriman' => ['nullable', 'numeric', 'min:0'],
            'catatan'              => ['nullable', 'string'],
        ]);

        $fg = FinishGoodItem::findOrFail($validated['itemable_id']);

        $salesOrder->update([
            'customer_id'          => $validated['customer_id'],
            'itemable_id'          => $fg->id,
            'kode_material'        => $fg->kode_material_produk,
            'product_name'         => $fg->nama_barang,
            'no_po_customer'       => $validated['no_po_customer'] ?? null,
            'tanggal_po'           => $validated['tanggal_po'],
            'lead_time'            => $validated['lead_time'] ?? '21 Hari Kerja',
            'qty'                  => (float) $validated['qty'],
            'harga_pcs'            => (float) $validated['harga_pcs'],
            'diskon'               => (float) ($validated['diskon'] ?? 0),
            'syarat_pembayaran'    => $validated['syarat_pembayaran'] ?? '30 Hari setelah invoice',
            'toleransi_pengiriman' => (float) ($validated['toleransi_pengiriman'] ?? 0),
            'catatan'              => $validated['catatan'] ?? null,
        ]);

        return response()->json([
            'message' => "Sales Order {$salesOrder->no_sales_order} berhasil diperbarui.",
            'data'    => $salesOrder->fresh(['customer', 'itemable']),
        ]);
    }

    public function destroy(SalesOrder $salesOrder): JsonResponse
    {
        if ($salesOrder->suratJalans()->exists()) {
            return response()->json([
                'message' => 'Sales Order tidak dapat dihapus karena sudah memiliki Surat Jalan pengiriman.',
            ], 422);
        }

        $salesOrder->delete();

        return response()->json([
            'message' => 'Sales Order berhasil di-Cut Off.',
        ]);
    }
}

