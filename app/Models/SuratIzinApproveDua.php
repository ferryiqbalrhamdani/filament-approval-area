<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratIzinApproveDua extends Model
{
    use HasFactory;

    protected $fillable = [
        'surat_izin_id',
        'surat_izin_approve_id',
        'user_id',
        'status',
        'keterangan',
    ];

    public function suratIzin()
    {
        return $this->belongsTo(SuratIzin::class);
    }

    public function suratIzinApprove()
    {
        return $this->belongsTo(SuratIzinApprove::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function suratIzinApproveTiga()
    {
        return $this->hasOne(suratIzinApproveTiga::class);
    }
}
