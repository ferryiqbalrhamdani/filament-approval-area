<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IzinLembur extends Model
{
    use HasFactory;

    protected $table = 'tb_lembur';

    protected $fillable = [
        'tarif_lembur_id',
        'tanggal_lembur',
        'start_time',
        'end_time',
        'keterangan_lembur',
        'total',
        'user_id',
        'lama_lembur',
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($izinLembur) {
            $izinLembur->izinLemburApprove()->delete();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tarifLembur()
    {
        return $this->belongsTo(TarifLembur::class);
    }

    public function izinLemburApprove()
    {
        return $this->hasOne(IzinLemburApprove::class);
    }
}
