<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">Edit {{ $beritaAcara->no_ba }}</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('berita-acaras.update', $beritaAcara) }}" enctype="multipart/form-data" class="rounded bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')
            @include('berita-acara.form', ['noBa' => $beritaAcara->no_ba])
            <div class="mt-6 flex gap-3"><button class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white">Simpan Perubahan</button><a href="{{ route('berita-acaras.show', $beritaAcara) }}" class="rounded border px-4 py-2 text-sm">Batal</a></div>
        </form>
    </div></div>
</x-app-layout>