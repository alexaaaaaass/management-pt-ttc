<x-filament-panels::page>

    {{ $this->form }}

    <div class="mt-6">
        <x-filament::button
            wire:click="hitung"
            color="success"
            icon="heroicon-o-calculator"
        >
            Hitung
        </x-filament::button>
    </div>

</x-filament-panels::page>