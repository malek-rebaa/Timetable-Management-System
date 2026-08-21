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
     * Enregistre les séances placées dans un emploi du temps existant.
     *
     * @param  Timetable  $timetable  emploi du temps cible
     * @param  PlacedSession[]  $placedSessions  sessions à persister
     * @return Timetable
     */
    public function commit(Timetable $timetable, array $placedSessions): Timetable
    {
        // Validation simple : vérifier qu'il y a des séances à insérer
        if (empty($placedSessions)) {
            $this->markFailed($timetable);
            throw new \InvalidArgumentException('Aucune séance à insérer dans l\'emploi du temps.');
        }

        return DB::transaction(function () use ($timetable, $placedSessions) {
            // Supprimer d'abord l'ancien EDT (uniquement les séances non verrouillées)
            $this->clearTimetable($timetable);

            $timetable->status = 'COMPLETED';
            $timetable->save();

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

            // Validation finale : s'assurer que le bon nombre de séances a été inséré
            $actualCount = $timetable->academicSessions()->where('is_locked', false)->count();
            if ($actualCount !== count($placedSessions)) {
                throw new \RuntimeException(
                    sprintf(
                        'Erreur de cohérence : %d séances attendues, %d insérées.',
                        count($placedSessions),
                        $actualCount
                    )
                );
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