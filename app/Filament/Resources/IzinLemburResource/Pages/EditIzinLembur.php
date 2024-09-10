<?php

namespace App\Filament\Resources\IzinLemburResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use App\Models\TarifLembur;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\IzinLemburResource;

class EditIzinLembur extends EditRecord
{
    protected static string $resource = IzinLemburResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Parse start and end time
        $jamMulai = Carbon::parse($data['start_time']);
        $sampaiJam = Carbon::parse($data['end_time']);

        // If the end time is earlier than the start time, it means the time has crossed midnight
        if ($sampaiJam->lessThan($jamMulai)) {
            $sampaiJam->addDay(); // Add a day to the end time
        }

        // Menghitung selisih waktu dalam jam
        $diffInHours = $jamMulai->diffInHours($sampaiJam);

        $selisih = (int)$diffInHours;

        $data['lama_lembur'] = (int)$diffInHours;
        $data['total'] = 0;

        // Menentukan apakah hari tersebut weekend atau weekday
        $tanggalLembur = Carbon::parse($data['tanggal_lembur']);
        $statusHari = $tanggalLembur->isWeekend() ? 'Weekend' : 'Weekday';

        // Mencari tarif lembur yang sesuai
        $tarifLembur = TarifLembur::where('status_hari', $statusHari)
            ->where(function ($query) use ($selisih) {
                if ($selisih >= 8) {
                    // Jika lama lembur >= 8 jam, cari tarif lumsum
                    $query->where('is_lumsum', true);
                } else {
                    // Jika < 8 jam, cari berdasarkan lama lembur atau operator '>='
                    $query->where('lama_lembur', $selisih);
                }
            })
            ->orderBy('lama_lembur', 'desc')
            ->first();

        $data['tarif_lembur_id'] = $tarifLembur->id;

        return $data;
    }
}
