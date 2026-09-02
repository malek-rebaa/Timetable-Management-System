<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('teacher_profiles', function (Blueprint $table) {
            $table->id();
            // Référence logique vers edutime_master.users, sans FK inter-base.
            $table->unsignedBigInteger('master_user_id')->unique();
            $table->string('employee_number')->nullable()->unique();
            $table->string('specialty')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::connection('tenant')->create('teacher_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['teacher_profile_id', 'subject_id']);
        });

        Schema::connection('tenant')->create('subject_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sessions_per_week');
            $table->unsignedInteger('session_duration');
            $table->enum('teaching_type', ['THEORY', 'TP'])->default('THEORY');
            $table->timestamps();

            $table->unique(
                ['academic_year_id', 'level_id', 'subject_id', 'teaching_type'],
                'subject_plans_year_level_subject_type_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('subject_plans');
        Schema::connection('tenant')->dropIfExists('teacher_subject');
        Schema::connection('tenant')->dropIfExists('teacher_profiles');
    }
};
