<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_sessions', function (Blueprint $table) {
            // Rattachement à une génération d'emploi du temps (nullable : saisie manuelle sans timetable)
            $table->foreignId('timetable_id')->nullable()->after('id')->constrained()->nullOnDelete();

            // Verrouillage d'une séance manuelle : la génération ne doit jamais la modifier
            $table->boolean('is_locked')->default(false)->after('group_number');

            // Convention : THEORY = groupe NULL ; TP = groupe 1 ou 2
            $table->integer('group_number')->unsigned()->nullable()->default(null)->change();
        });

        // Filet de sécurité : une séance ne peut pas finir avant de commencer
        DB::statement('ALTER TABLE academic_sessions ADD CONSTRAINT academic_sessions_end_time_check CHECK (end_time > start_time)');

        // Bornage des groupes : NULL (THEORY) ou 1/2 (TP)
        DB::statement('ALTER TABLE academic_sessions ADD CONSTRAINT academic_sessions_group_number_check CHECK (group_number IS NULL OR group_number IN (1, 2))');

        Schema::table('academic_sessions', function (Blueprint $table) {
            // Index composites de détection de conflit (non-chevauchement)
            $table->index(['teacher_id', 'day', 'start_time', 'end_time'], 'academic_sessions_teacher_slot_idx');
            $table->index(['class_room_id', 'day', 'start_time', 'end_time'], 'academic_sessions_class_slot_idx');
            $table->index(['room_id', 'day', 'start_time', 'end_time'], 'academic_sessions_room_slot_idx');
            // Régénération ciblée par plan/groupe
            $table->index(['subject_plan_id', 'group_number'], 'academic_sessions_plan_group_idx');
        });
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE academic_sessions DROP CONSTRAINT academic_sessions_end_time_check');
        DB::statement('ALTER TABLE academic_sessions DROP CONSTRAINT academic_sessions_group_number_check');

        Schema::table('academic_sessions', function (Blueprint $table) {
            $table->dropForeign(['timetable_id']);
            $table->dropColumn(['timetable_id', 'is_locked']);
            $table->dropIndex('academic_sessions_teacher_slot_idx');
            $table->dropIndex('academic_sessions_class_slot_idx');
            $table->dropIndex('academic_sessions_room_slot_idx');
            $table->dropIndex('academic_sessions_plan_group_idx');

            // Restauration de la convention d'origine (group_number non-nullable, défaut 1)
            $table->integer('group_number')->unsigned()->default(1)->change();
        });
    }
};
