<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presensi', function (Blueprint $table) {
            $table->increments('presensi_id');
            $table->unsignedInteger('siswa_id');
            $table->date('tgl_presensi');
            $table->time('jam_in')->nullable();
            $table->time('jam_out')->nullable();
            $table->string('lokasi_in', 255)->nullable();
            $table->string('lokasi_out', 255)->nullable();
            $table->string('foto_in', 255)->nullable();
            $table->string('foto_out', 255)->nullable();
            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'alpha']);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->unique(['siswa_id', 'tgl_presensi']);
            $table->foreign('siswa_id')->references('siswa_id')->on('siswa')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi');
    }
};
