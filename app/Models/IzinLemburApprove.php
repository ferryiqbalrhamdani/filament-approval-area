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

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function izinLembur()
    {
        return $this->belongsTo(IzinLembur::class);
    }

    public function izinLemburApproveDua()
    {
        return $this->hasOne(IzinLemburApproveDua::class);
    }
}
