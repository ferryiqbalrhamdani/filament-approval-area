<?php

namespace App\Filament\Clusters\IzinCuti\Resources\CutiKhususResource\Pages;

use App\Filament\Clusters\IzinCuti\Resources\CutiKhususResource;
use App\Models\IzinCutiApprove;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCutiKhusus extends CreateRecord
{
    protected static string $resource = CutiKhususResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $data['company_id'] = Auth::user()->company_id;
        $data['user_id'] = Auth::user()->id;

        if ($data['pilihan_cuti'] == 'Menikah') {
            $tanggalMulaiCuti = Carbon::parse($data['mulai_cuti']);
            $daysToAdd = 2; // Only add 2 more days because the first day is included
            $tanggalSelesaiCuti = $tanggalMulaiCuti->copy();

            // Add days, skipping weekends
            while ($daysToAdd > 0) {
                $tanggalSelesaiCuti->addDay();
                // If it's not a Saturday (6) or Sunday (7), decrement daysToAdd
                if (!$tanggalSelesaiCuti->isWeekend()) {
                    $daysToAdd--;
                }
            }

            $data['mulai_cuti'] = $tanggalMulaiCuti->format('Y-m-d');
            $data['sampai_cuti'] = $tanggalSelesaiCuti->format('Y-m-d');
            $data['lama_cuti'] = '3 Hari';
        } elseif ($data['pilihan_cuti'] == 'Cuti Melahirkan') {
            $tanggalMulaiCuti = Carbon::parse($data['mulai_cuti']);
            $tanggalSelesaiCuti = $tanggalMulaiCuti->copy()->addMonths(2);

            $data['mulai_cuti'] = $tanggalMulaiCuti->format('Y-m-d');
            $data['sampai_cuti'] = $tanggalSelesaiCuti->format('Y-m-d');
            $data['lama_cuti'] = '3 Bulan';
        } else {
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
        }

        // hari


        // dd($data);

        return $data;
    }

    protected function afterCreate(): void
    {
        $cutiKhusus = $this->record;

        IzinCutiApprove::create([
            'cuti_khusus_id' => $cutiKhusus->id,
            'keterangan_cuti' => 'Cuti Khusus',
            'user_cuti_id' => Auth::user()->id,
            'company_id' => $cutiKhusus->company_id,
            'pilihan_cuti' => $cutiKhusus->pilihan_cuti,
            'lama_cuti' => $cutiKhusus->lama_cuti,
            'mulai_cuti' => $cutiKhusus->mulai_cuti,
            'sampai_cuti' => $cutiKhusus->sampai_cuti,
            'pesan_cuti' => $cutiKhusus->keterangan_cuti,

        ]);

        // $this->redirect($this->getRedirectUrl());
    }
}
