<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';
    protected $primaryKey = 'siswa_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'nis',
        'nama_lengkap',
        'kelas',
        'angkatan_id',
        'no_hp',
        'foto',
        'alamat',
        'tanggal_lahir',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Relasi ke Angkatan
    public function angkatan()
    {
        return $this->belongsTo(Angkatan::class, 'angkatan_id', 'angkatan_id');
    }

    // Relasi ke Presensi
    public function presensi()
    {
        return $this->hasMany(Presensi::class, 'siswa_id', 'siswa_id');
    }
}
