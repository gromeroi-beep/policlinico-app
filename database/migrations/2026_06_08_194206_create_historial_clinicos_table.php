<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('historiales_clinicos', function (Blueprint $table) {
            $table->id();

            // FK al paciente — solo usuarios con role='paciente'
            $table->foreignId('paciente_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Código único de historial (ej: HC-00001)
            $table->string('codigo_historial')->unique();

            $table->string('grupo_sanguineo', 5)->nullable(); // A+, O-, etc.
            $table->text('alergias')->nullable();
            $table->text('diagnostico_principal')->nullable();
            $table->text('antecedentes_medicos')->nullable();

            // ENUM de estado clínico del paciente
            $table->enum('estado_paciente', ['Estable', 'En Tratamiento', 'Crítico'])
                  ->default('Estable');

            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('historiales_clinicos');
    }
};