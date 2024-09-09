<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IzinCutiApproveTiga extends Model
{
    use HasFactory;

    protected $fillable = [
        'izin_cuti_approve_dua_id',
        'user_id',
        'status',
        'keterangan',
    ];

    public function izinCutiApproveDua()
    {
        return $this->belongsTo(IzinCutiApproveDua::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
