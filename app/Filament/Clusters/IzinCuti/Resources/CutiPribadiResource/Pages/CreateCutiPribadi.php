<?php

namespace App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource\Pages;

use App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource;
use App\Models\IzinCutiApprove;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCutiPribadi extends CreateRecord
{
    protected static string $resource = CutiPribadiResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = Auth::user()->company_id;
        $data['user_id'] = Auth::user()->id;

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

        // dd($data);

        return $data;
    }

    protected function afterCreate(): void
    {
        $cutiPribadi = $this->record;

        IzinCutiApprove::create([
            'cuti_pribadi_id' => $cutiPribadi->id,
            'keterangan_cuti' => 'Cuti Pribadi',
            'user_cuti_id' => Auth::user()->id,
            'company_id' => Auth::user()->company_id,
            'lama_cuti' => $cutiPribadi->lama_cuti,
            'mulai_cuti' => $cutiPribadi->mulai_cuti,
            'sampai_cuti' => $cutiPribadi->sampai_cuti,
            'pesan_cuti' => $cutiPribadi->keterangan_cuti,
        ]);

        // $this->redirect($this->getRedirectUrl());
    }
}
