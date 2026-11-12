<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru', function (Blueprint $table) {
            $table->increments('guru_id');
            $table->unsignedInteger('user_id');
            $table->string('nama_lengkap');
            $table->string('no_hp', 20);
            $table->string('email', 100);
            $table->foreign('user_id')->references('user_id')->on('user')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru');
    }
};
