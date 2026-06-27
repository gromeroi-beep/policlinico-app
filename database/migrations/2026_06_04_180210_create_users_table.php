<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('username')->unique()->nullable(); // solo admin
            $table->string('email')->unique()->nullable();
            $table->string('password')->nullable();
            $table->enum('role', ['admin', 'medico', 'paciente']);
            // Campos médico
            $table->string('colegiatura')->nullable();
            $table->foreignId('especialidad_id')->nullable()->constrained('especialidades')->onDelete('set null');
            // Campos paciente
            $table->enum('tipo_doc', ['DNI', 'CE'])->nullable();
            $table->string('num_doc')->unique()->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};