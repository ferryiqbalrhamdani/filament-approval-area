<?php

namespace App\Filament\Resources\SuratIzinResource\Pages;

use App\Filament\Resources\SuratIzinResource;
use App\Models\SuratIzinApprove;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateSuratIzin extends CreateRecord
{
    protected static string $resource = SuratIzinResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $data['company_id'] = Auth::user()->company_id;

        if (empty($data['sampai_tanggal'])) {
            $data['sampai_tanggal'] = $data['tanggal_izin'];
        }

        if ($data['keperluan_izin'] == 'Izin Datang Terlambat') {
            $data['jam_izin'] = '08:00';
        }
        if ($data['keperluan_izin'] == 'Izin Tidak Masuk Kerja') {
            $data['jam_izin'] = NULL;
            $data['sampai_jam'] = NULL;
        }
        if ($data['keperluan_izin'] == 'Tugas Meninggalkan Kantor' && $data['status_izin'] == 'lebih_dari_sehari') {
            $data['jam_izin'] = NULL;
            $data['sampai_jam'] = NULL;
        }


        // Parsing waktu mulai dan selesai
        $jamMulai = Carbon::parse($data['jam_izin']);
        $sampaiJam = Carbon::parse($data['sampai_jam']);

        // Menghitung selisih waktu
        $diff = $jamMulai->diff($sampaiJam);

        // Menghitung selisih jam dan menit
        $diffInHours = $diff->h;
        $diffInMinutes = $diff->i;

        // Menyimpan user ID yang sedang login
        $data['user_id'] = auth()->id();

        // Mengatur durasi izin berdasarkan selisih waktu
        if ($diffInHours > 0 && $diffInMinutes > 0) {
            $data['durasi_izin'] = $diffInHours . " Jam " . $diffInMinutes . " Menit";
        } elseif ($diffInHours > 0 && $diffInMinutes == 0) {
            $data['durasi_izin'] = $diffInHours . " Jam";
        } elseif ($diffInHours == 0 && $diffInMinutes > 0) {
            $data['durasi_izin'] = $diffInMinutes . " Menit";
        } else {
            $data['durasi_izin'] = "";
        }


        // hari
        $tanggalIzin = Carbon::parse($data['tanggal_izin']);
        $sampaiTanggal = Carbon::parse($data['sampai_tanggal']);

        $lamaIzin = 0;
        $currentDate = $tanggalIzin->copy();

        while ($currentDate <= $sampaiTanggal) {
            if ($currentDate->isWeekday()) {
                $lamaIzin++;
            }
            $currentDate->addDay();
        }

        $data['lama_izin'] = $lamaIzin . " Hari";



        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }


    protected function afterCreate(): void
    {
        $suratIzin = $this->record;

        SuratIzinApprove::create([
            'surat_izin_id' => $suratIzin->id,
        ]);

        $this->redirect($this->getRedirectUrl());
    }
}
