<?php

namespace App\Http\Controllers\Api\V1\Master;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Customer::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_customer', 'like', "%{$search}%")
                  ->orWhere('nama_customer', 'like', "%{$search}%")
                  ->orWhere('nama_pic', 'like', "%{$search}%")
                  ->orWhere('nama_group', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 15);
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

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode_customer'  => ['required', 'string', 'max:50', 'unique:customers,kode_customer'],
            'nama_customer'  => ['required', 'string', 'max:255'],
            'alamat_lengkap' => ['required', 'string'],
            'alamat_kedua'   => ['nullable', 'string'],
            'alamat_ketiga'  => ['nullable', 'string'],
            'kode_group'     => ['required', 'string', 'max:100'],
            'nama_group'     => ['required', 'string', 'max:255'],
            'nama_pic'       => ['required', 'string', 'max:255'],
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'message' => 'Customer berhasil ditambahkan.',
            'data'    => $customer,
        ], 201);
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'kode_customer'  => ['required', 'string', 'max:50', 'unique:customers,kode_customer,' . $customer->id],
            'nama_customer'  => ['required', 'string', 'max:255'],
            'alamat_lengkap' => ['required', 'string'],
            'alamat_kedua'   => ['nullable', 'string'],
            'alamat_ketiga'  => ['nullable', 'string'],
            'kode_group'     => ['required', 'string', 'max:100'],
            'nama_group'     => ['required', 'string', 'max:255'],
            'nama_pic'       => ['required', 'string', 'max:255'],
        ]);

        $customer->update($validated);

        return response()->json([
            'message' => 'Customer berhasil diperbarui.',
            'data'    => $customer,
        ]);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json([
            'message' => 'Customer berhasil dihapus.',
        ]);
    }
}
