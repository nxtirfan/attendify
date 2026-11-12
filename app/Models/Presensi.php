<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    protected $table = 'presensi';
    protected $primaryKey = 'presensi_id';
    public $timestamps = false;

    protected $fillable = [
        'siswa_id',
        'tgl_presensi',
        'jam_in',
        'jam_out',
        'foto_in',
        'foto_out',
        'lokasi_in',
        'lokasi_out',
        'status',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id', 'siswa_id');
    }
}
