<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Console\Command;

class UpdateLeaveBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-leave-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cuti berhasil di update';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::where('status_karyawan', 'tetap')->get();

        foreach ($users as $user) {
            $joinDate = Carbon::parse($user->tgl_pengangkatan); // Tanggal pengangkatan
            $today = Carbon::today();

            if ($joinDate) {
                // 1. Tambah cuti setiap tahun pada hari pengangkatan
                if ($today->isSameDay($joinDate)) {
                    if ($user->cuti <= 6) {
                        $user->cuti += 6;
                    }
                }

                // 2. Cek reset cuti setiap 1 tahun 6 bulan dari pengangkatan dan seterusnya
                $resetDate = $joinDate->copy()->addMonths(18); // Tanggal pertama reset 1 tahun 6 bulan setelah pengangkatan

                // Loop untuk cek apakah reset berlaku setiap 18 bulan setelah tanggal pengangkatan
                while ($resetDate->lessThanOrEqualTo($today)) {
                    if ($today->isSameDay($resetDate)) {
                        // Reset jika cuti lebih dari 6
                        if ($user->cuti > 6) {
                            $user->cuti = 6;
                        }
                    }
                    // Tambah 18 bulan untuk reset berikutnya
                    $resetDate->addMonths(18);
                }

                // Simpan perubahan cuti
                $user->save();
            }
        }
    }
}
