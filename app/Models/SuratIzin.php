<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratIzin extends Model
{
    use HasFactory;

    protected $table = 'tb_izin';

    protected $fillable = [
        'user_id',
        'keperluan_izin',
        'photo',
        'lama_izin',
        'tanggal_izin',
        'sampai_tanggal',
        'durasi_izin',
        'jam_izin',
        'sampai_jam',
        'keterangan_izin',
        'company_id',
        'status_izin',
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($suratIzin) {
            $suratIzin->suratIzinApprove()->delete();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function suratIzinApprove()
    {
        return $this->hasOne(SuratIzinApprove::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
