<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La base réelle peut ne pas avoir l'ancien index unique (level_id, subject_id)
        // selon son historique. On ne le supprime que s'il existe.
        Schema::table('subject_plans', function (Blueprint $table) {
            if (Schema::hasIndex('subject_plans', ['level_id', 'subject_id'])) {
                $table->dropUnique(['level_id', 'subject_id']);
            }
        });

        // weekly_hours est dérivable : sessions_per_week × session_duration → on le supprime
        Schema::table('subject_plans', function (Blueprint $table) {
            if (Schema::hasColumn('subject_plans', 'weekly_hours')) {
                $table->dropColumn('weekly_hours');
            }
        });

        // Autorise un plan THEORY ET un plan TP pour la même matière et le même niveau
        Schema::table('subject_plans', function (Blueprint $table) {
            $table->unique(['level_id', 'subject_id', 'teaching_type'], 'subject_plans_level_subject_type_unique');
        });

        // Garde-fous : volume et durée positifs (MariaDB >= 10.2 applique les CHECK)
        DB::statement('ALTER TABLE subject_plans ADD CONSTRAINT subject_plans_sessions_per_week_check CHECK (sessions_per_week > 0)');
        DB::statement('ALTER TABLE subject_plans ADD CONSTRAINT subject_plans_session_duration_check CHECK (session_duration > 0)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE subject_plans DROP CONSTRAINT subject_plans_sessions_per_week_check');
        DB::statement('ALTER TABLE subject_plans DROP CONSTRAINT subject_plans_session_duration_check');

        Schema::table('subject_plans', function (Blueprint $table) {
            $table->dropUnique('subject_plans_level_subject_type_unique');
            if (! Schema::hasColumn('subject_plans', 'weekly_hours')) {
                $table->unsignedInteger('weekly_hours')->default(0);
            }
            if (! Schema::hasIndex('subject_plans', ['level_id', 'subject_id'])) {
                $table->unique(['level_id', 'subject_id'], 'subject_plans_level_id_subject_id_unique');
            }
        });
    }
};
