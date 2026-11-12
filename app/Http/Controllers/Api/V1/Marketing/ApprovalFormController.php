<?php

namespace App\Http\Controllers\Api\V1\Marketing;

use App\Http\Controllers\Controller;
use App\Models\ApprovalForm;
use App\Models\ApprovalFormItem;
use App\Models\Customer;
use App\Models\FinishGoodItem;
use App\Services\Marketing\MarketingWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalFormController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ApprovalForm::with(['customer', 'items.finishGoodItem', 'orderConfirmation']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('form_no', 'like', "%{$search}%")
                  ->orWhere('order_id', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($cq) => $cq->where('nama_customer', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        $perPage = (int) $request->input('per_page', 15);
        $data = $query->latest('id')->paginate($perPage);

        $rows = collect($data->items())->map(function (ApprovalForm $af) {
            return array_merge($af->toArray(), [
                'bom_status'            => $af->bom_status,
                'has_oc'                => (bool) $af->orderConfirmation,
                'order_confirmation_id' => $af->orderConfirmation?->id,
                'oc_doc_no'             => $af->orderConfirmation?->doc_no,
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
            'customers' => Customer::select('id', 'kode_customer', 'nama_customer', 'nama_pic')
                ->orderBy('nama_customer')
                ->get(),
            'finish_goods' => FinishGoodItem::select(
                'id', 'customer_id', 'kode_material_produk', 'nama_barang',
                'jenis', 'komponen', 'isi', 'spesifikasi_kertas', 'warna', 'finishing'
            )->orderBy('nama_barang')->get(),
        ]);
    }

    public function store(Request $request, MarketingWorkflowService $workflow): JsonResponse
    {
        $validated = $request->validate([
            'customer_id'         => ['required', 'exists:customers,id'],
            'tanggal'             => ['required', 'date'],
            'lead_time'           => ['nullable', 'string', 'max:100'],
            'catatan_produksi'    => ['nullable', 'string'],
            'disetujui_marketing' => ['nullable', 'string', 'max:255'],
            'disetujui_produksi'  => ['nullable', 'string', 'max:255'],
            'status'              => ['nullable', 'in:draft,approved,converted_to_oc'],
            'auto_create_fg'      => ['nullable', 'boolean'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.finish_good_item_id' => ['nullable', 'exists:finish_good_items,id'],
            'items.*.item_name'           => ['required', 'string', 'max:255'],
            'items.*.jenis'               => ['nullable', 'string', 'max:100'],
            'items.*.komponen'            => ['nullable', 'string', 'max:100'],
            'items.*.isi'                 => ['nullable', 'string', 'max:100'],
            'items.*.kertas'              => ['nullable', 'string', 'max:255'],
            'items.*.warna'               => ['nullable', 'string', 'max:255'],
            'items.*.finishing'           => ['nullable', 'string', 'max:255'],
            'items.*.qty'                 => ['required', 'numeric', 'min:1'],
            'items.*.satuan'              => ['nullable', 'string', 'max:50'],
            'items.*.cek_desain'          => ['nullable', 'boolean'],
            'items.*.cek_plate'           => ['nullable', 'boolean'],
            'items.*.cek_produksi'        => ['nullable', 'boolean'],
            'items.*.cek_qc'              => ['nullable', 'boolean'],
        ]);

        $af = DB::transaction(function () use ($validated, $workflow) {
            $customer = Customer::findOrFail($validated['customer_id']);
            $orderId = ApprovalForm::generateOrderId($customer);
            $formNo = 'AF-' . substr($orderId, 3);

            $af = ApprovalForm::create([
                'order_id'            => $orderId,
                'form_no'             => $formNo,
                'customer_id'         => $customer->id,
                'tanggal'             => $validated['tanggal'],
                'lead_time'           => $validated['lead_time'] ?? '21 Hari Kerja',
                'catatan_produksi'    => $validated['catatan_produksi'] ?? null,
                'disetujui_marketing' => $validated['disetujui_marketing'] ?? auth()->user()?->name,
                'disetujui_produksi'  => $validated['disetujui_produksi'] ?? null,
                'status'              => $validated['status'] ?? 'approved',
                'created_by'          => auth()->id(),
            ]);

            $autoCreateFg = (bool) ($validated['auto_create_fg'] ?? true);

            foreach ($validated['items'] as $row) {
                $item = ApprovalFormItem::create([
                    'approval_form_id'    => $af->id,
                    'finish_good_item_id' => $row['finish_good_item_id'] ?? null,
                    'item_name'           => $row['item_name'],
                    'jenis'               => $row['jenis'] ?? null,
                    'komponen'            => $row['komponen'] ?? null,
                    'isi'                 => $row['isi'] ?? null,
                    'kertas'              => $row['kertas'] ?? null,
                    'warna'               => $row['warna'] ?? null,
                    'finishing'           => $row['finishing'] ?? null,
                    'qty'                 => (float) $row['qty'],
                    'satuan'              => $row['satuan'] ?? 'Pcs',
                    'cek_desain'          => (bool) ($row['cek_desain'] ?? false),
                    'cek_plate'           => (bool) ($row['cek_plate'] ?? false),
                    'cek_produksi'        => (bool) ($row['cek_produksi'] ?? false),
                    'cek_qc'              => (bool) ($row['cek_qc'] ?? false),
                ]);

                if ($autoCreateFg && empty($item->finish_good_item_id)) {
                    $workflow->createOrLinkFinishGoodFromApprovalFormItem($item);
                }
            }

            return $af->load(['customer', 'items.finishGoodItem', 'orderConfirmation']);
        });

        return response()->json([
            'message' => "Approval Form {$af->form_no} berhasil dibuat.",
            'data'    => $af,
        ], 201);
    }

    public function update(Request $request, ApprovalForm $approvalForm, MarketingWorkflowService $workflow): JsonResponse
    {
        $validated = $request->validate([
            'customer_id'         => ['required', 'exists:customers,id'],
            'tanggal'             => ['required', 'date'],
            'lead_time'           => ['nullable', 'string', 'max:100'],
            'catatan_produksi'    => ['nullable', 'string'],
            'disetujui_marketing' => ['nullable', 'string', 'max:255'],
            'disetujui_produksi'  => ['nullable', 'string', 'max:255'],
            'status'              => ['nullable', 'in:draft,approved,converted_to_oc'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.finish_good_item_id' => ['nullable', 'exists:finish_good_items,id'],
            'items.*.item_name'           => ['required', 'string', 'max:255'],
            'items.*.jenis'               => ['nullable', 'string', 'max:100'],
            'items.*.komponen'            => ['nullable', 'string', 'max:100'],
            'items.*.isi'                 => ['nullable', 'string', 'max:100'],
            'items.*.kertas'              => ['nullable', 'string', 'max:255'],
            'items.*.warna'               => ['nullable', 'string', 'max:255'],
            'items.*.finishing'           => ['nullable', 'string', 'max:255'],
            'items.*.qty'                 => ['required', 'numeric', 'min:1'],
            'items.*.satuan'              => ['nullable', 'string', 'max:50'],
            'items.*.cek_desain'          => ['nullable', 'boolean'],
            'items.*.cek_plate'           => ['nullable', 'boolean'],
            'items.*.cek_produksi'        => ['nullable', 'boolean'],
            'items.*.cek_qc'              => ['nullable', 'boolean'],
        ]);

        $updated = DB::transaction(function () use ($approvalForm, $validated, $workflow) {
            $approvalForm->update([
                'customer_id'         => $validated['customer_id'],
                'tanggal'             => $validated['tanggal'],
                'lead_time'           => $validated['lead_time'] ?? '21 Hari Kerja',
                'catatan_produksi'    => $validated['catatan_produksi'] ?? null,
                'disetujui_marketing' => $validated['disetujui_marketing'] ?? null,
                'disetujui_produksi'  => $validated['disetujui_produksi'] ?? null,
                'status'              => $validated['status'] ?? $approvalForm->status,
            ]);

            $approvalForm->items()->delete();

            foreach ($validated['items'] as $row) {
                $item = ApprovalFormItem::create([
                    'approval_form_id'    => $approvalForm->id,
                    'finish_good_item_id' => $row['finish_good_item_id'] ?? null,
                    'item_name'           => $row['item_name'],
                    'jenis'               => $row['jenis'] ?? null,
                    'komponen'            => $row['komponen'] ?? null,
                    'isi'                 => $row['isi'] ?? null,
                    'kertas'              => $row['kertas'] ?? null,
                    'warna'               => $row['warna'] ?? null,
                    'finishing'           => $row['finishing'] ?? null,
                    'qty'                 => (float) $row['qty'],
                    'satuan'              => $row['satuan'] ?? 'Pcs',
                    'cek_desain'          => (bool) ($row['cek_desain'] ?? false),
                    'cek_plate'           => (bool) ($row['cek_plate'] ?? false),
                    'cek_produksi'        => (bool) ($row['cek_produksi'] ?? false),
                    'cek_qc'              => (bool) ($row['cek_qc'] ?? false),
                ]);

                if (empty($item->finish_good_item_id)) {
                    $workflow->createOrLinkFinishGoodFromApprovalFormItem($item);
                }
            }

            return $approvalForm->load(['customer', 'items.finishGoodItem', 'orderConfirmation']);
        });

        return response()->json([
            'message' => "Approval Form {$updated->form_no} berhasil diperbarui.",
            'data'    => $updated,
        ]);
    }

    public function destroy(ApprovalForm $approvalForm): JsonResponse
    {
        if ($approvalForm->orderConfirmation()->exists()) {
            return response()->json([
                'message' => 'Approval Form tidak dapat dihapus karena sudah memiliki Order Confirmation.',
            ], 422);
        }

        $approvalForm->delete();

        return response()->json([
            'message' => 'Approval Form berhasil dihapus.',
        ]);
    }

    public function convertToOc(ApprovalForm $approvalForm, MarketingWorkflowService $workflow): JsonResponse
    {
        $oc = $workflow->generateOrderConfirmationFromApprovalForm($approvalForm);

        return response()->json([
            'message' => "Order Confirmation {$oc->doc_no} berhasil diterbitkan dari AF {$approvalForm->form_no}.",
            'data'    => $oc->load(['customer', 'items']),
        ]);
    }
}
