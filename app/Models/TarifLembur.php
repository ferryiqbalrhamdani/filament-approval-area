<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TarifLembur extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_hari',
        'operator',
        'lama_lembur',
        'tarif_lembur_perjam',
        'uang_makan',
        'is_lumsum',
        'tarif_lumsum',
    ];

    public function izinLembur()
    {
        return $this->hasOne(IzinLembur::class);
    }
}
