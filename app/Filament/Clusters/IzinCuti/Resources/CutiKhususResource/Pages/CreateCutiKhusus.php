<?php

namespace App\Filament\Clusters\IzinCuti\Resources\CutiKhususResource\Pages;

use App\Filament\Clusters\IzinCuti\Resources\CutiKhususResource;
use App\Models\IzinCutiApprove;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Notifications\Notification;
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
        } elseif ($data['pilihan_cuti'] == 'Menikahkan Anak' || $data['pilihan_cuti'] == 'Mengkhitankan/Membaptiskan Anak' || $data['pilihan_cuti'] == 'Suami/Istri/Anak/Orangtua/Mertua/Menantu Meninggal' || $data['pilihan_cuti'] == 'Istri Melahirkan, Keguguran Kandungan') {
            $tanggalMulaiCuti = Carbon::parse($data['mulai_cuti']);
            $daysToAdd = 1;
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
            $data['lama_cuti'] = '2 Hari';
        } elseif ($data['pilihan_cuti'] == 'Cuti Melahirkan') {
            $tanggalMulaiCuti = Carbon::parse($data['mulai_cuti']);
            $tanggalSelesaiCuti = $tanggalMulaiCuti->copy()->addMonths(2);

            $data['mulai_cuti'] = $tanggalMulaiCuti->format('Y-m-d');
            $data['sampai_cuti'] = $tanggalSelesaiCuti->format('Y-m-d');
            $data['lama_cuti'] = '3 Bulan';
        } else {
            $tanggalIzin = Carbon::parse($data['mulai_cuti']);
            $sampaiTanggal = Carbon::parse($data['mulai_cuti']);


            $data['mulai_cuti'] = $tanggalIzin->format('Y-m-d');
            $data['sampai_cuti'] = $sampaiTanggal->format('Y-m-d');

            // Set a default value for 'lama_cuti', such as '1 Hari'.
            $data['lama_cuti'] = "1 Hari";
        }


        return $data;
    }


    protected function afterCreate(): void
    {
        $cutiKhusus = $this->record;

        // Step 1: Create the first approval stage (cutiKhususApprove) linked to cutiKhusus
        $cutiKhusus = $cutiKhusus->izinCutiApprove()->create([
            'cuti_khusus_id' => $cutiKhusus->id,
            'keterangan_cuti' => 'Cuti Khusus',
            'user_cuti_id' => Auth::user()->id,
            'company_id' => $cutiKhusus->company_id,
            'pilihan_cuti' => $cutiKhusus->pilihan_cuti,
            'lama_cuti' => $cutiKhusus->lama_cuti,
            'mulai_cuti' => $cutiKhusus->mulai_cuti,
            'sampai_cuti' => $cutiKhusus->sampai_cuti,
            'pesan_cuti' => $cutiKhusus->keterangan_cuti,
            'user_id' => Auth::user()->user_approve_id,

        ]);

        // Step 2: Create the second approval stage (cutiKhususApproveDua) linked to cutiKhususApprove
        $cutiKhususDua = $cutiKhusus->izinCutiApproveDua()->create([
            'cuti_khusus_approve_id' => $cutiKhusus->id,
            'user_id' => Auth::user()->user_approve_dua_id,
        ]);

        // Step 3: Create the third approval stage (cutiKhususApproveTiga) linked to cutiKhususApproveDua and cutiKhususApprove
        $cutiKhususDua->izinCutiApproveTiga()->create([
            'cuti_khusus_approve_dua_id' => $cutiKhususDua->id,
        ]);

        // $this->redirect($this->getRedirectUrl());
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
