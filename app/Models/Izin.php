<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Izin extends Model
{
    protected $table = 'izin';
    protected $primaryKey = 'izin_id';
    public $timestamps = false;

    protected $fillable = [
        'siswa_id',
        'tgl_izin',
        'keterangan',
        'status',
        'status_approved',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id', 'siswa_id');
    }
}
