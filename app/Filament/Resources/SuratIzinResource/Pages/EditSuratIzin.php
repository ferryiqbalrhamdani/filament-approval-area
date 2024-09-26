<?php

namespace App\Filament\Resources\SuratIzinResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\SuratIzinResource;

class EditSuratIzin extends EditRecord
{
    protected static string $resource = SuratIzinResource::class;


    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Check and adjust 'sampai_tanggal'
        if (empty($data['sampai_tanggal'])) {
            $data['sampai_tanggal'] = $data['tanggal_izin'];
        }

        // Adjust time based on 'keperluan_izin'
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

        // Parse and calculate the duration of the leave
        $jamMulai = Carbon::parse($data['jam_izin']);
        $sampaiJam = Carbon::parse($data['sampai_jam']);
        $diff = $jamMulai->diff($sampaiJam);
        $diffInHours = $diff->h;
        $diffInMinutes = $diff->i;

        // Set 'durasi_izin' based on the time difference
        if ($diffInHours > 0 && $diffInMinutes > 0) {
            $data['durasi_izin'] = $diffInHours . " Jam " . $diffInMinutes . " Menit";
        } elseif ($diffInHours > 0 && $diffInMinutes == 0) {
            $data['durasi_izin'] = $diffInHours . " Jam";
        } elseif ($diffInHours == 0 && $diffInMinutes > 0) {
            $data['durasi_izin'] = $diffInMinutes . " Menit";
        } else {
            $data['durasi_izin'] = "";
        }

        // Calculate leave days ('lama_izin')
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

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
