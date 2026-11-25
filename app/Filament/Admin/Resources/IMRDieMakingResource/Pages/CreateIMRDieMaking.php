<?php

namespace App\Filament\Admin\Resources\IMRDieMakingResource\Pages;

use App\Filament\Admin\Resources\IMRDieMakingResource;
use App\Models\IMRPrinting;
use App\Services\IMR\IMRPrintingService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateIMRDieMaking extends CreateRecord
{
    protected static string $resource = IMRDieMakingResource::class;

    public function mount(): void
    {
        parent::mount();

        $spkId = request()->query('spk_id');
        if ($spkId) {
            $this->form->fill(array_merge($this->form->getRawState(), [
                'spk_id' => (int) $spkId,
            ]));
        }
    }

    protected function handleRecordCreation(array $data): Model
    {
        $data['stasiun'] = IMRPrinting::STASIUN_DIEMAKING;
        $data['request_plate'] = false;

        return app(IMRPrintingService::class)->create($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
