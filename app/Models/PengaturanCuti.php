<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengaturanCuti extends Model
{
    use HasFactory;

    protected $fillable = [
        'defualt_cuti',
        'reset_cuti',
        'tanggal_reset',
    ];
}
