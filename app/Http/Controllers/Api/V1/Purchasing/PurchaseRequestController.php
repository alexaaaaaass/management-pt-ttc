<?php

namespace App\Http\Controllers\Api\V1\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\MasterDepartemen;
use App\Models\MasterItem;
use App\Models\PurchaseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PurchaseRequest::with(['departemen', 'items.item.satuan']);

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pr', 'like', "%{$search}%")
                  ->orWhereHas('departemen', fn ($dq) => $dq->where('nama_departemen', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        $perPage = min(100, max(5, $request->integer('per_page', 15)));
        $data = $query->latest('id')->paginate($perPage);

        return response()->json([
            'data' => $data->items(),
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
            'departemens' => MasterDepartemen::select('id', 'nama_departemen')->orderBy('nama_departemen')->get(),
            'items'       => MasterItem::with('satuan:id,nama_satuan')
                ->select('id', 'kode_master_item', 'nama_master_item', 'satuan_id')
                ->orderBy('nama_master_item')
                ->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'departemen_id'    => ['required', 'exists:master_departemens,id'],
            'tanggal_pr'       => ['required', 'date'],
            'nomor_pr'         => ['nullable', 'string', 'max:100', 'unique:purchase_requests,nomor_pr'],
            'status'           => ['nullable', 'in:otorisasi,deotorisasi'],
            'items'            => ['required', 'array', 'min:1'],
            'items.*.item_id'  => ['required', 'exists:master_items,id'],
            'items.*.qty'      => ['required', 'numeric', 'min:0.01'],
            'items.*.satuan'   => ['nullable', 'string', 'max:50'],
            'items.*.eta'      => ['nullable', 'date'],
            'items.*.catatan'  => ['nullable', 'string'],
        ]);

        $pr = DB::transaction(function () use ($validated) {
            $nomorPr = $validated['nomor_pr']
                ?: ('PR/TTC/' . date('Y/m') . '/' . str_pad((PurchaseRequest::count() + 1), 3, '0', STR_PAD_LEFT));

            $pr = PurchaseRequest::create([
                'nomor_pr'      => $nomorPr,
                'departemen_id' => $validated['departemen_id'],
                'tanggal_pr'    => $validated['tanggal_pr'],
                'status'        => $validated['status'] ?? 'deotorisasi',
            ]);

            foreach ($validated['items'] as $row) {
                $masterItem = MasterItem::with('satuan')->find($row['item_id']);
                $pr->items()->create([
                    'item_id' => $row['item_id'],
                    'qty'     => (float) $row['qty'],
                    'satuan'  => $row['satuan'] ?? $masterItem?->satuan?->nama_satuan ?? 'PCS',
                    'eta'     => $row['eta'] ?? null,
                    'catatan' => $row['catatan'] ?? null,
                ]);
            }

            return $pr->load(['departemen', 'items.item']);
        });

        return response()->json([
            'message' => "Purchase Request {$pr->nomor_pr} berhasil dibuat.",
            'data'    => $pr,
        ], 201);
    }

    public function authorizeToggle(PurchaseRequest $purchaseRequest): JsonResponse
    {
        $newStatus = $purchaseRequest->status === 'otorisasi' ? 'deotorisasi' : 'otorisasi';
        $purchaseRequest->update(['status' => $newStatus]);

        return response()->json([
            'message' => "Status Purchase Request diubah menjadi {$newStatus}.",
            'data'    => $purchaseRequest->fresh(['departemen', 'items.item']),
        ]);
    }

    public function destroy(PurchaseRequest $purchaseRequest): JsonResponse
    {
        if ($purchaseRequest->purchaseOrders()->exists()) {
            return response()->json([
                'message' => 'Purchase Request tidak dapat dihapus karena sudah memiliki Purchase Order terkait.',
            ], 422);
        }

        $purchaseRequest->delete();

        return response()->json([
            'message' => 'Purchase Request berhasil dihapus.',
        ]);
    }
}
