<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('angkatan', function (Blueprint $table) {
            $table->unsignedInteger('angkatan_id')->primary();
            $table->string('nama_angkatan', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('angkatan');
    }
};
