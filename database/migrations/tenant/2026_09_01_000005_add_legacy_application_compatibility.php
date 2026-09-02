<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('tenant');

        $schema->table('class_rooms', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->nullable()->change();
        });

        $schema->table('subject_plans', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->nullable()->change();
        });

        $schema->table('timetables', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->nullable()->change();
            $table->string('academic_year')->nullable()->after('name');
            $table->string('semester')->nullable()->after('academic_year');
        });

        $schema->table('teacher_subject', function (Blueprint $table) {
            $table->foreignId('teacher_profile_id')->nullable()->change();
            $table->unsignedBigInteger('teacher_id')->nullable()->after('id');
            $table->unique(['teacher_id', 'subject_id'], 'teacher_subject_teacher_user_unique');
        });

        $schema->table('academic_sessions', function (Blueprint $table) {
            $table->foreignId('teacher_profile_id')->nullable()->change();
            $table->unsignedBigInteger('teacher_id')->nullable()->after('subject_plan_id');
            $table->index(['teacher_id', 'day', 'start_time', 'end_time'], 'sessions_teacher_user_slot_idx');
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('tenant');

        $schema->table('academic_sessions', function (Blueprint $table) {
            $table->dropIndex('sessions_teacher_user_slot_idx');
            $table->dropColumn('teacher_id');
            $table->foreignId('teacher_profile_id')->nullable(false)->change();
        });

        $schema->table('teacher_subject', function (Blueprint $table) {
            $table->dropUnique('teacher_subject_teacher_user_unique');
            $table->dropColumn('teacher_id');
            $table->foreignId('teacher_profile_id')->nullable(false)->change();
        });

        $schema->table('timetables', function (Blueprint $table) {
            $table->dropColumn(['academic_year', 'semester']);
            $table->foreignId('academic_year_id')->nullable(false)->change();
        });

        $schema->table('subject_plans', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->nullable(false)->change();
        });

        $schema->table('class_rooms', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->nullable(false)->change();
        });
    }
};
