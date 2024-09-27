<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CutiPribadi extends Model
{
    use HasFactory;

    protected $table = 'tb_cuti_pribadi';

    protected $fillable = [
        'user_id',
        'company_id',
        'lama_cuti',
        'mulai_cuti',
        'sampai_cuti',
        'keterangan_cuti',
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($cutiPribadi) {
            if ($cutiPribadi->photo) {
                // Hapus file dari storage jika ada photo
                Storage::disk('public')->delete($cutiPribadi->photo);
            }
            $cutiPribadi->izinCutiApprove()->delete();
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
        return $this->hasOne(IzinCutiApprove::class, 'cuti_pribadi_id');
    }
}
