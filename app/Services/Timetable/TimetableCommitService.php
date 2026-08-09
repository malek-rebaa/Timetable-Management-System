<?php

namespace App\Services\Timetable;

use App\Models\AcademicSession;
use App\Models\Timetable;
use App\Services\Timetable\DTO\PlacedSession;
use Illuminate\Support\Facades\DB;

/**
 * Persiste le résultat d'une génération dans la base de données.
 *
 * Utilise toujours DB::transaction() pour garantir la cohérence.
 * Ne modifie jamais les séances verrouillées (is_locked = true).
 */
class TimetableCommitService
{
    /**
     * Enregistre les séances placées dans un nouvel emploi du temps.
     *
     * @param  array<string>  $name  nom de l'emploi du temps
     * @param  string|null  $academicYear  année académique
     * @param  string|null  $semester  semestre
     * @param  PlacedSession[]  $placedSessions  sessions à persister
     * @return Timetable
     */
    public function commit(string $name, ?string $academicYear, ?string $semester, array $placedSessions): Timetable
    {
        return DB::transaction(function () use ($name, $academicYear, $semester, $placedSessions) {
            $timetable = Timetable::create([
                'name' => $name,
                'academic_year' => $academicYear,
                'semester' => $semester,
                'status' => 'COMPLETED',
            ]);

            foreach ($placedSessions as $ps) {
                AcademicSession::create([
                    'timetable_id' => $timetable->id,
                    'subject_plan_id' => $ps->subjectPlanId,
                    'teacher_id' => $ps->teacherId,
                    'class_room_id' => $ps->classRoomId,
                    'room_id' => $ps->roomId,
                    'day' => $ps->day,
                    'start_time' => $ps->startTime,
                    'end_time' => $ps->endTime,
                    'group_number' => $ps->groupNumber,
                    'is_locked' => false,
                ]);
            }

            return $timetable->fresh();
        });
    }

    /**
     * Marque un emploi du temps comme échoué.
     */
    public function markFailed(Timetable $timetable): void
    {
        $timetable->status = 'FAILED';
        $timetable->save();
    }

    /**
     * Supprime toutes les séances non verrouillées d'un emploi du temps.
     * Utile avant une régénération.
     */
    public function clearTimetable(Timetable $timetable): int
    {
        return $timetable->academicSessions()
            ->where('is_locked', false)
            ->delete();
    }
}