<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->integer('weekly_hours')->unsigned();
            $table->integer('sessions_per_week')->unsigned();
            $table->integer('session_duration')->unsigned();
            $table->enum('teaching_type', ['THEORY', 'TP'])->default('THEORY');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_plans');
    }
};
