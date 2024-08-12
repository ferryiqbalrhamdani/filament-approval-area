<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IzinLemburApproveTiga extends Model
{
    use HasFactory;

    protected $fillable = [
        'izin_lembur_approve_dua_id',
        'user_id',
        'status',
        'keterangan',
    ];

    public function izinLemburApproveDua()
    {
        return $this->belongsTo(IzinLemburApproveDua::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
