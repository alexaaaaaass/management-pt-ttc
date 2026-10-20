<?php

namespace App\Http\Controllers;

use App\Models\BeritaAcara;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BeritaAcaraController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = $request->query('status');

        $beritaAcaras = BeritaAcara::query()
            ->with('customer')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('no_ba', 'like', '%' . $search . '%')
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('nama_customer', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when(in_array($status, ['draft', 'proses', 'selesai'], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest('tanggal')
            ->paginate(15)
            ->withQueryString();

        return view('berita-acara.index', compact('beritaAcaras', 'search', 'status'));
    }

    public function create(): View
    {
        return view('berita-acara.create', [
            'customers' => Customer::orderBy('nama_customer')->get(),
            'noBa' => BeritaAcara::generateNoBA(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $uploads = $data['foto_bukti'] ?? [];
        unset($data['foto_bukti']);

        $data['no_ba'] = $data['no_ba'] ?: BeritaAcara::generateNoBA();
        $data['foto_bukti'] = $this->storePhotos($uploads);

        $beritaAcara = BeritaAcara::create($data);

        return redirect()->route('berita-acaras.show', $beritaAcara)
            ->with('success', 'Berita acara berhasil dibuat.');
    }

    public function show(BeritaAcara $beritaAcara): View
    {
        return view('berita-acara.show', [
            'beritaAcara' => $beritaAcara->load('customer'),
        ]);
    }

    public function edit(BeritaAcara $beritaAcara): View
    {
        return view('berita-acara.edit', [
            'beritaAcara' => $beritaAcara,
            'customers' => Customer::orderBy('nama_customer')->get(),
        ]);
    }

    public function update(Request $request, BeritaAcara $beritaAcara): RedirectResponse
    {
        $data = $this->validatedData($request, $beritaAcara);
        $uploads = $data['foto_bukti'] ?? [];
        unset($data['foto_bukti']);

        $data['no_ba'] = $data['no_ba'] ?: $beritaAcara->no_ba;
        $data['foto_bukti'] = array_merge(
            $beritaAcara->foto_bukti ?? [],
            $this->storePhotos($uploads)
        );

        $beritaAcara->update($data);

        return redirect()->route('berita-acaras.show', $beritaAcara)
            ->with('success', 'Berita acara berhasil diperbarui.');
    }

    public function destroy(BeritaAcara $beritaAcara): RedirectResponse
    {
        $beritaAcara->delete();

        return redirect()->route('berita-acaras.index')
            ->with('success', 'Berita acara berhasil dihapus.');
    }

    private function validatedData(Request $request, ?BeritaAcara $beritaAcara = null): array
    {
        return $request->validate([
            'no_ba' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('berita_acaras', 'no_ba')->ignore($beritaAcara?->id),
            ],
            'tipe' => ['required', Rule::in(['rapat', 'complaint_produksi'])],
            'tanggal' => ['required', 'date'],
            'no_surat_jalan_invoice' => ['nullable', 'string', 'max:255'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'item_product' => ['nullable', 'string', 'max:255'],
            'foto_bukti' => ['nullable', 'array', 'max:10'],
            'foto_bukti.*' => ['image', 'max:5120'],
            'kronologi' => ['nullable', 'string'],
            'analisa_produksi' => ['nullable', 'string'],
            'tindakan_solusi' => ['nullable', 'string'],
            'peserta' => ['nullable', 'string'],
            'agenda' => ['nullable', 'string'],
            'catatan' => ['nullable', 'string'],
            'ttd_qc' => ['nullable', 'string', 'max:255'],
            'ttd_customer' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'proses', 'selesai'])],
        ]);
    }

    private function storePhotos(array $uploads): array
    {
        return collect($uploads)
            ->filter()
            ->map(fn ($photo) => $photo->store('berita-acara', 'public'))
            ->all();
    }
}