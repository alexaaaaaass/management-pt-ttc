<?php

namespace App\Filament\Admin\Resources\IMRPrintingResource\Pages;

use App\Filament\Admin\Resources\IMRPrintingResource;
use App\Filament\Admin\Resources\IMRPrintingResource\Schemas\PlateRequestSection;
use App\Services\IMR\IMRPrintingService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateIMRPrinting extends CreateRecord
{
    protected static string $resource = IMRPrintingResource::class;

    public function mount(): void
    {
        parent::mount();

        $spkId = request()->query('spk_id');
        if ($spkId) {
            $plateState = PlateRequestSection::resolvePlateStateForSpk((int) $spkId);
            $this->form->fill(array_merge($this->form->getRawState(), [
                'spk_id'              => (int) $spkId,
                'pracetak_process_id' => $plateState['pracetak_process_id'],
                'plate_archive_id'    => $plateState['plate_archive_id'],
                'request_plate'       => $plateState['request_plate'],
                'plate_qty_requested' => $plateState['plate_qty_requested'],
            ]));
        }
    }

    protected function handleRecordCreation(array $data): Model
    {
        return app(IMRPrintingService::class)
            ->create($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
