<?php

namespace App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource;

class EditCutiPribadi extends EditRecord
{
    protected static string $resource = CutiPribadiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $tanggalIzin = Carbon::parse($data['mulai_cuti']);
        $sampaiTanggal = Carbon::parse($data['sampai_cuti']);

        $lamaIzin = 0;
        $currentDate = $tanggalIzin->copy();

        while ($currentDate <= $sampaiTanggal) {
            if ($currentDate->isWeekday()) {
                $lamaIzin++;
            }
            $currentDate->addDay();
        }

        $data['lama_cuti'] = $lamaIzin . " Hari";

        return $data;
    }
}
