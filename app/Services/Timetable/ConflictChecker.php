<?php

namespace App\Services\Timetable;

use App\Models\AcademicSession;
use App\Models\ClassRoom;
use App\Models\Room;
use App\Models\SubjectPlan;
use App\Models\User;
use App\Multitenancy\CurrentTenant;

use App\Services\Timetable\Contracts\ConstraintCheckerInterface;

/**
 * Point de vérité unique pour toutes les règles métier de placement.
 *
 * Utilisé par :
 * - la saisie manuelle d'une séance (FormRequest -> contrôleur);
 * - la modification d'une séance;
 *
 * Cette implémentation effectue des requêtes SQL pour valider en base.
 */
class ConflictChecker implements ConstraintCheckerInterface
{
    public function __construct(protected SlotGrid $grid)
    {
    }

    /**
     * Vérifie toutes les contraintes pour une séance candidate.
     *
     * @param  AcademicSession  $candidate  séance à valider (peut être non persistée)
     * @param  int|null  $ignoreSessionId  séance à exclure de la vérification (cas de modification)
     * @return array<int, string>  liste de messages d'erreur (vide si OK)
     */
    public function check(AcademicSession $candidate, ?int $ignoreSessionId = null): array
    {
        $errors = [];

        $errors = array_merge($errors, $this->checkTeacher($candidate));
        $errors = array_merge($errors, $this->checkRoom($candidate));
        $errors = array_merge($errors, $this->checkOverlaps($candidate, $ignoreSessionId));
        $errors = array_merge($errors, $this->checkGroupConsistency($candidate));
        $errors = array_merge($errors, $this->checkClassRoomCompatibility($candidate));
        $errors = array_merge($errors, $this->checkDurationAndGrid($candidate));

        return $errors;
    }

    /**
     * Règle : l'enseignant doit être habilité pour la matière du plan.
     */
    public function checkTeacher(AcademicSession $candidate): array
    {
        /** @var SubjectPlan|null $plan */
        $plan = $candidate->subjectPlan;

        if (! $plan) {
            return ['Le plan de cours est introuvable.'];
        }

        $teacher = User::forSchool(app(CurrentTenant::class)->requireSchool()->getKey())
            ->whereKey($candidate->teacher_id)
            ->where('role', 'TEACHER')
            ->where('is_active', true)
            ->first();

        if ($teacher === null) {
            return ['La séance doit être assurée par un enseignant.'];
        }

        $isEligible = \DB::connection('tenant')->table('teacher_subject')
            ->where('teacher_id', $candidate->teacher_id)
            ->where('subject_id', $plan->subject_id)
            ->exists();

        if (! $isEligible) {
            return [
                sprintf(
                    "L'enseignant n'est pas habilité à enseigner la matière « %s ».",
                    $plan->subject?->name ?? $plan->subject_id
                ),
            ];
        }

        return [];
    }

    /**
     * Règles salle : type compatible avec le type d'enseignement + capacité suffisante.
     */
    public function checkRoom(AcademicSession $candidate): array
    {
        $plan = $candidate->subjectPlan;
        $room = $candidate->room;

        if (! $room) {
            return ['Une salle est obligatoire pour chaque séance.'];
        }

        $allowed = config("timetable.room_types.{$plan->teaching_type}", []);

        if (! in_array($room->type, $allowed, true)) {
            return [
                sprintf(
                    'Une séance de type %s ne peut pas se dérouler dans une salle de type %s.',
                    $plan->teaching_type,
                    $room->type
                ),
            ];
        }

        $class = $candidate->classRoom;
        $tpGroups = (int) config('timetable.tp_groups', 2);
        $requiredCapacity = $plan->teaching_type === 'TP'
            ? (int) ceil(($class?->student_count ?? 0) / $tpGroups)
            : ($class?->student_count ?? 0);

        if ($room->capacity < $requiredCapacity) {
            return [
                sprintf(
                    'La salle « %s » a une capacité de %d places, insuffisante pour %d étudiants.',
                    $room->name,
                    $room->capacity,
                    $requiredCapacity
                ),
            ];
        }

        return [];
    }

    /**
     * Règles de non-chevauchement : enseignant, salle, classe.
     * Limité au même emploi du temps si timetable_id est défini.
     */
    public function checkOverlaps(AcademicSession $candidate, ?int $ignoreSessionId = null): array
    {
        $errors = [];
        $day = $candidate->day;
        $start = $candidate->start_time;
        $end = $candidate->end_time;

        $query = AcademicSession::query()
            ->where('day', $day)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start);

        if ($candidate->timetable_id) {
            $query->where('timetable_id', $candidate->timetable_id);
        }

        if ($ignoreSessionId) {
            $query->where('id', '!=', $ignoreSessionId);
        }

        $teacherOverlap = (clone $query)
            ->where('teacher_id', $candidate->teacher_id)
            ->exists();

        if ($teacherOverlap) {
            $errors[] = "L'enseignant a déjà une séance sur ce créneau.";
        }

        if ($candidate->room_id) {
            $roomOverlap = (clone $query)
                ->where('room_id', $candidate->room_id)
                ->exists();

            if ($roomOverlap) {
                $errors[] = 'La salle est déjà occupée sur ce créneau.';
            }
        }

        $classOverlap = (clone $query)
            ->where('class_room_id', $candidate->class_room_id)
            ->exists();

        if ($classOverlap) {
            $errors[] = 'La classe a déjà une séance sur ce créneau.';
        }

        return $errors;
    }

    /**
     * Règle de cohérence du groupe selon le type d'enseignement :
     * THEORY -> groupe NULL ; TP -> groupe 1 ou 2.
     */
    public function checkGroupConsistency(AcademicSession $candidate): array
    {
        $teachingType = $candidate->subjectPlan?->teaching_type;

        if ($teachingType === 'THEORY' && $candidate->group_number !== null) {
            return ['Une séance de cours (THEORY) doit avoir un groupe null (classe entière).'];
        }

        if ($teachingType === 'TP') {
            $maxGroups = (int) config('timetable.tp_groups', 2);
            $group = $candidate->group_number;

            if ($group === null || $group < 1 || $group > $maxGroups) {
                return [sprintf('Une séance de TP doit appartenir au groupe 1 ou 2 (max %d).', $maxGroups)];
            }
        }

        return [];
    }

    /**
     * Vérifie si une salle est compatible pour un type d'enseignement donné.
     */
    public function checkClassRoomCompatibility(AcademicSession $candidate): array
    {
        $plan = $candidate->subjectPlan;
        $classRoom = $candidate->classRoom;

        if ($plan && $classRoom && $plan->level_id !== $classRoom->level_id) {
            return ['Le plan de cours ne correspond pas au niveau de cette classe.'];
        }

        return [];
    }

    public function checkDurationAndGrid(AcademicSession $candidate): array
    {
        $plan = $candidate->subjectPlan;
        if (! $plan || ! $candidate->day || ! $candidate->start_time || ! $candidate->end_time) {
            return [];
        }

        $duration = (int) \Carbon\Carbon::parse($candidate->start_time)
            ->diffInMinutes(\Carbon\Carbon::parse($candidate->end_time), true);

        if ($duration !== (int) $plan->session_duration) {
            return [sprintf('La durée de la séance doit être de %d minutes.', $plan->session_duration)];
        }

        if (! $this->grid->isValidSpan($candidate->day, $candidate->start_time, $candidate->end_time, $duration)) {
            return ['Le créneau doit être aligné sur la grille et ne peut pas traverser une pause.'];
        }

        return [];
    }

    public function isRoomCompatible(Room $room, string $teachingType): bool
    {
        return in_array($room->type, config("timetable.room_types.{$teachingType}", []), true);
    }

    /**
     * Vérifie si un enseignant est habilité pour une matière.
     */
    public function isTeacherEligible(User $teacher, int $subjectId): bool
    {
        return \DB::connection('tenant')->table('teacher_subject')
            ->where('teacher_id', $teacher->id)
            ->where('subject_id', $subjectId)
            ->exists();
    }
}
