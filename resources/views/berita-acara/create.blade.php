<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">Buat Berita Acara</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('berita-acaras.store') }}" enctype="multipart/form-data" class="rounded bg-white p-6 shadow-sm">
            @csrf
            @include('berita-acara.form', ['beritaAcara' => null, 'noBa' => $noBa])
            <div class="mt-6 flex gap-3"><button class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white">Simpan</button><a href="{{ route('berita-acaras.index') }}" class="rounded border px-4 py-2 text-sm">Batal</a></div>
        </form>
    </div></div>
</x-app-layout>