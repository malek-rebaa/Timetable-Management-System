<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('levels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('class_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('student_count')->default(0);
            $table->timestamps();

            $table->unique(['academic_year_id', 'level_id', 'name']);
        });

        Schema::connection('tenant')->create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('capacity');
            $table->enum('type', ['CLASSROOM', 'LABORATORY', 'AMPHITHEATER'])->default('CLASSROOM');
            $table->timestamps();
        });

        Schema::connection('tenant')->create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('subjects');
        Schema::connection('tenant')->dropIfExists('rooms');
        Schema::connection('tenant')->dropIfExists('class_rooms');
        Schema::connection('tenant')->dropIfExists('levels');
    }
};
