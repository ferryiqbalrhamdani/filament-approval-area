<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratIzinApproveTiga extends Model
{
    use HasFactory;

    protected $fillable = [
        'surat_izin_approve_dua_id',
        'user_id',
        'status',
        'keterangan',
    ];

    public function suratIzinApproveDua()
    {
        return $this->belongsTo(SuratIzinApproveDua::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
