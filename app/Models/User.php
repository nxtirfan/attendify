<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'user'; // disesuaikan karena nama tabel kamu huruf besar

    protected $primaryKey = 'user_id';

    public $timestamps = false;

    protected $fillable = [
        'username',
        'password',
        'role',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    // Relasi ke Siswa
    public function siswa()
    {
        return $this->hasOne(Siswa::class, 'user_id', 'user_id');
    }

    // Relasi ke Guru
    public function guru()
    {
        return $this->hasOne(Guru::class, 'user_id', 'user_id');
    }
}
