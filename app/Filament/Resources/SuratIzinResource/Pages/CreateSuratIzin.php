<?php

namespace App\Filament\Resources\SuratIzinResource\Pages;

use App\Filament\Resources\SuratIzinResource;
use App\Models\SuratIzinApprove;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSuratIzin extends CreateRecord
{
    protected static string $resource = SuratIzinResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        if (empty($data['sampai_tanggal'])) {
            $data['sampai_tanggal'] = $data['tanggal_izin'];
        }

        if ($data['keperluan_izin'] == 'Izin Datang Terlambat') {
            $data['jam_izin'] = '08:00';
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
        if ($diffInHours > 0) {
            $data['durasi_izin'] = $diffInHours . " Jam " . $diffInMinutes . " Menit";
        } else {
            $data['durasi_izin'] = $diffInMinutes . " Menit";
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

    protected function afterCreate(): void
    {
        $suratIzin = $this->record;

        SuratIzinApprove::create([
            'surat_izin_id' => $suratIzin->id,
        ]);
    }
}
