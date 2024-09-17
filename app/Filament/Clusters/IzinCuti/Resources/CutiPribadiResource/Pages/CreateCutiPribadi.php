<?php

namespace App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource\Pages;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;
use App\Filament\Clusters\IzinCuti\Resources\CutiPribadiResource;

class CreateCutiPribadi extends CreateRecord
{
    protected static string $resource = CutiPribadiResource::class;

    // Fungsi untuk menghitung lama cuti tanpa akhir pekan
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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        // Menyimpan data company_id dan user_id
        $data['company_id'] = $user->company_id;
        $data['user_id'] = $user->id;

        // Memanggil fungsi hitungLamaIzin
        $lamaIzin = $this->hitungLamaIzin($data['mulai_cuti'], $data['sampai_cuti']);

        // Memeriksa apakah jumlah hari cuti melebihi cuti yang tersedia
        if ($lamaIzin > $user->cuti) {
            Notification::make()
                ->title('Kesalahan')
                ->danger()
                ->body("Anda tidak memiliki cukup jatah cuti. Anda mengajukan $lamaIzin hari, namun hanya tersedia {$user->cuti} hari.")
                ->duration(15000)
                ->send();

            // Mencegah pengiriman form
            throw ValidationException::withMessages([
                'mulai_cuti' => 'Jatah cuti Anda tidak mencukupi untuk pengajuan ini.',
            ]);
        }

        // Mengupdate sisa cuti user
        User::where('id', $user->id)->update([
            'cuti' => $user->cuti - $lamaIzin,
        ]);

        $data['lama_cuti'] = $lamaIzin . " Hari";

        return $data;
    }

    protected function afterCreate(): void
    {
        $cutiPribadi = $this->record;

        // Step 1: Membuat persetujuan pertama (izinCutiApprove) menggunakan cuti_pribadi_id
        $cutiPribadiApprove = $cutiPribadi->izinCutiApprove()->create([
            'cuti_pribadi_id' => $cutiPribadi->id,
            'keterangan_cuti' => 'Cuti Pribadi',
            'user_cuti_id' => Auth::user()->id,
            'company_id' => Auth::user()->company_id,
            'lama_cuti' => $cutiPribadi->lama_cuti,
            'mulai_cuti' => $cutiPribadi->mulai_cuti,
            'sampai_cuti' => $cutiPribadi->sampai_cuti,
            'pesan_cuti' => $cutiPribadi->keterangan_cuti,
        ]);

        // Step 2: Membuat persetujuan kedua (izinCutiApproveDua)
        $cutiPribadiApproveDua = $cutiPribadiApprove->izinCutiApproveDua()->create([
            'cuti_pribadi_approve_id' => $cutiPribadiApprove->id,
        ]);

        // Step 3: Membuat persetujuan ketiga (izinCutiApproveTiga)
        $cutiPribadiApproveDua->izinCutiApproveTiga()->create([
            'cuti_pribadi_approve_dua_id' => $cutiPribadiApproveDua->id,
        ]);

        // Redirect setelah berhasil membuat record
        // $this->redirect($this->getRedirectUrl());
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
