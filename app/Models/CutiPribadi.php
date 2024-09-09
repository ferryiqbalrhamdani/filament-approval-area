<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
