<?php

namespace App\Http\Controllers\Api\V1\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\ImrPrinting;
use App\Services\Inventory\IssueMaterialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IssueMaterialController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ImrPrinting::with(['spk', 'departemen', 'items.item.stock'])
            ->whereIn('status', ['Approved', 'Partial Issued']);

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('no_imr', 'like', "%{$search}%")
                  ->orWhereHas('spk', fn ($sq) => $sq->where('no_spk', 'like', "%{$search}%"));
            });
        }

        $perPage = min(100, max(5, $request->integer('per_page', 15)));
        $data = $query->latest('id')->paginate($perPage);

        $rows = collect($data->items())->map(function (ImrPrinting $imr) {
            return array_merge($imr->toArray(), [
                'total_approved' => (float) $imr->items->sum('qty_approved'),
                'total_issued'   => (float) $imr->items->sum('qty_issued'),
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

    public function issue(Request $request, ImrPrinting $imrPrinting, IssueMaterialService $issueService): JsonResponse
    {
        $validated = $request->validate([
            'items'             => ['required', 'array', 'min:1'],
            'items.*.qty_issue' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $issueService->issue($imrPrinting, $validated);

            return response()->json([
                'message' => "Pengeluaran material untuk {$imrPrinting->no_imr} berhasil diproses.",
                'data'    => $imrPrinting->fresh(['spk', 'departemen', 'items.item.stock']),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
