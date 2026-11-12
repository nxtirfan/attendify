<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Angkatan extends Model
{
    protected $table = 'angkatan';
    protected $primaryKey = 'angkatan_id';
    public $timestamps = false;

    protected $fillable = [
        'angkatan_id',
        'nama_angkatan',
    ];
}
