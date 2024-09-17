<?php

namespace App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource\Pages;

use Carbon\Carbon;
use App\Models\User;
use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;
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

    private function hitungLamaIzin($tanggalMulai, $tanggalSampai)
    {
        $lamaIzin = 0;
        $currentDate = Carbon::parse($tanggalMulai)->copy();
        $sampaiTanggal = Carbon::parse($tanggalSampai);

        // Menghitung jumlah hari cuti tanpa akhir pekan
        while ($currentDate <= $sampaiTanggal) {
            if ($currentDate->isWeekday()) {
                $lamaIzin++;
            }
            $currentDate->addDay();
        }

        return $lamaIzin;
    }

    // protected function mutateFormDataBeforeSave(array $data): array
    // {
    //     // Memanggil fungsi hitungLamaIzin
    //     $lamaIzin = $this->hitungLamaIzin($data['mulai_cuti'], $data['sampai_cuti']);

    //     $data['lama_cuti'] = $lamaIzin . " Hari";

    //     dd($data);

    //     return $data;
    // }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Memanggil fungsi hitungLamaIzin
        $lamaIzin = $this->hitungLamaIzin($data['mulai_cuti'], $data['sampai_cuti']);
        $sisaCuti = explode(" ", $record->lama_cuti);
        $cutiUser = Auth::user()->cuti;

        $cuti = ($cutiUser + (int)$sisaCuti[0]) - $lamaIzin;
        $hasilCuti = (int)$sisaCuti[0] + $cuti;

        // Memeriksa apakah jumlah hari cuti melebihi cuti yang tersedia
        if ($cuti < 0) {
            Notification::make()
                ->title('Kesalahan')
                ->danger()
                ->body("Anda tidak memiliki cukup jatah cuti. Anda mengajukan $lamaIzin hari.")
                ->duration(15000)
                ->send();

            // Mencegah pengiriman form
            throw ValidationException::withMessages([
                'mulai_cuti' => 'Jatah cuti Anda tidak mencukupi untuk pengajuan ini.',
            ]);
        }

        // Mengupdate sisa cuti user

        $data['lama_cuti'] = $lamaIzin . " Hari";

        $record->update($data);
        User::where('id', $record->user_id)->update([
            'cuti' => $hasilCuti,
        ]);

        return $record;
    }

    protected function afterSave(): void
    {
        $cutiPribadi = $this->record;

        // Step 1: Membuat persetujuan pertama (izinCutiApprove) menggunakan cuti_pribadi_id
        $cutiPribadi->izinCutiApprove()->update([
            'cuti_pribadi_id' => $cutiPribadi->id,
            'keterangan_cuti' => 'Cuti Pribadi',
            'user_cuti_id' => Auth::user()->id,
            'company_id' => Auth::user()->company_id,
            'lama_cuti' => $cutiPribadi->lama_cuti,
            'mulai_cuti' => $cutiPribadi->mulai_cuti,
            'sampai_cuti' => $cutiPribadi->sampai_cuti,
            'pesan_cuti' => $cutiPribadi->keterangan_cuti,
        ]);
    }
}
