<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('izin', function (Blueprint $table) {
            $table->increments('izin_id');
            $table->unsignedInteger('siswa_id');
            $table->date('tgl_izin');
            $table->string('keterangan', 255)->nullable();
            $table->enum('status', ['izin', 'sakit']);
            $table->enum('status_approved', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->unique(['siswa_id', 'tgl_izin']);
            $table->foreign('siswa_id')->references('siswa_id')->on('siswa')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('izin');
    }
};
