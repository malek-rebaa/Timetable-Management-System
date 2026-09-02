<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('timetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->enum('status', ['PENDING', 'RUNNING', 'COMPLETED', 'FAILED'])->default('PENDING');
            // Référence logique vers edutime_master.users.
            $table->unsignedBigInteger('generated_by_user_id')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('academic_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subject_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('day', ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY']);
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedTinyInteger('group_number')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamps();

            $table->index(['teacher_profile_id', 'day', 'start_time', 'end_time'], 'sessions_teacher_slot_idx');
            $table->index(['class_room_id', 'day', 'start_time', 'end_time'], 'sessions_class_slot_idx');
            $table->index(['room_id', 'day', 'start_time', 'end_time'], 'sessions_room_slot_idx');
            $table->index(['subject_plan_id', 'group_number'], 'sessions_plan_group_idx');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('academic_sessions');
        Schema::connection('tenant')->dropIfExists('timetables');
    }
};
