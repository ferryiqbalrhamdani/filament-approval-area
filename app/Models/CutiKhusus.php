<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CutiKhusus extends Model
{
    use HasFactory;

    protected $table = 'tb_cuti_khusus';

    protected $fillable = [
        'user_id',
        'company_id',
        'lama_cuti',
        'pilihan_cuti',
        'mulai_cuti',
        'sampai_cuti',
        'keterangan_cuti',
    ];



    public static function boot()
    {
        parent::boot();

        static::deleting(function ($cutiKhusus) {
            if ($cutiKhusus->photo) {
                // Hapus file dari storage jika ada photo
                Storage::disk('public')->delete($cutiKhusus->photo);
            }
            $cutiKhusus->izinCutiApprove()->delete();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function izinCutiApprove()
    {
        return $this->hasOne(IzinCutiApprove::class, 'cuti_khusus_id');
    }
}
