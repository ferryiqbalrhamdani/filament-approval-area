<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IzinLemburApproveDua extends Model
{
    use HasFactory;

    protected $fillable = [
        'izin_lembur_approve_id',
        'user_id',
        'status',
        'keterangan',
    ];

    public function izinLemburApprove()
    {
        return $this->belongsTo(IzinLemburApprove::class);
    }

    public function izinLemburApproveTiga()
    {
        return $this->hasOne(IzinLemburApproveTiga::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
