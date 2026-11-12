<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user', function (Blueprint $table) {
            $table->increments('user_id');
            $table->string('username')->unique();
            $table->string('password');
            $table->enum('role', ['Siswa', 'Guru', 'Admin']);
            $table->string('remember_token', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};
