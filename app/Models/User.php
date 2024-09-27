<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'password',
        'company_id',
        'office_id',
        'position_id',
        'division_id',
        'jk',
        'status_karyawan',
        'cuti',
        'user_approve_id',
        'user_approve_dua_id',
        'tgl_pengangkatan',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
    public function userApprove()
    {
        return $this->belongsTo(User::class, 'user_approve_id');
    }

    public function userApproveDua()
    {
        return $this->belongsTo(User::class, 'user_approve_dua_id');
    }

    public function suratIzin()
    {
        return $this->hasMany(SuratIzin::class);
    }

    public function cuti()
    {
        return $this->hasMany(IzinCutiApprove::class);
    }

    public function lembur()
    {
        return $this->hasMany(IzinLembur::class);
    }


    public function userPasswordReset()
    {
        return $this->belongsTo(PasswordReset::class);
    }
}
