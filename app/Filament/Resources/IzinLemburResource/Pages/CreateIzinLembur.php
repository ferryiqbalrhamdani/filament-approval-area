<?php

namespace App\Filament\Resources\IzinLemburResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use App\Models\TarifLembur;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\IzinLemburResource;
use App\Models\IzinLemburApprove;

class CreateIzinLembur extends CreateRecord
{
    protected static string $resource = IzinLemburResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;

        // Parsing waktu mulai dan selesai
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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }


    protected function afterCreate(): void
    {
        $izinLembur = $this->record;

        // Step 1: Create the first approval stage (izinLemburApprove) linked to izinLembur
        $izinApprove = $izinLembur->izinLemburApprove()->create([
            'izin_lembur_id' => $izinLembur->id,
        ]);

        // Step 2: Create the second approval stage (izinLemburApproveDua) linked to izinLemburApprove
        $izinApproveDua = $izinApprove->izinLemburApproveDua()->create([
            'izin_lembur_approve_id' => $izinApprove->id,
        ]);

        // Step 3: Create the third approval stage (izinLemburApproveTiga) linked to izinLemburApproveDua and izinLemburApprove
        $izinApproveDua->izinLemburApproveTiga()->create([
            'izin_lembur_approve_dua_id' => $izinApproveDua->id,
        ]);

        // Redirect after successful creation
        $this->redirect($this->getRedirectUrl());
    }
}
