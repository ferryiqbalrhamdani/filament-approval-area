<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IzinLemburApprove extends Model
{
    use HasFactory;

    protected $fillable = [
        'izin_lembur_id',
        'user_id',
        'status',
        'keterangan'
    ];


    public function izinLembur()
    {
        return $this->belongsTo(IzinLembur::class);
    }
}
