<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswa', function (Blueprint $table) {
            $table->increments('siswa_id');
            $table->unsignedInteger('user_id');
            $table->string('nis', 20)->unique();
            $table->string('nama_lengkap');
            $table->string('kelas', 10);
            $table->unsignedInteger('angkatan_id')->nullable();
            $table->string('no_hp', 20);
            $table->string('foto', 50)->nullable();
            $table->string('alamat', 255)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->foreign('user_id')->references('user_id')->on('user')->cascadeOnDelete();
            $table->foreign('angkatan_id')->references('angkatan_id')->on('angkatan')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
