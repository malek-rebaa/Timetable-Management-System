<?php

namespace App\Services\Timetable\DTO;

use App\Models\ClassRoom;
use App\Models\SubjectPlan;

/**
 * Requête de placement : une séance à placer pour (classe, plan, groupe).
 * Contient les domaines pré-filtrés (enseignants et salles éligibles) pour
 * que l'algorithme ne vérifie que les disponibilités pendant la boucle.
 */
class SessionRequest
{
    public int $difficultyScore = 0;

    /**
     * @param  array<int, int>  $teacherIds  enseignants habilités pour la matière
     * @param  array<int, int>  $roomIds  salles compatibles (type + capacité)
     */
    public function __construct(
        public int $id,
        public int $levelId,
        public ClassRoom $classRoom,
        public SubjectPlan $subjectPlan,
        public ?int $groupNumber,
        public int $sessionsCount,
        public int $minCapacity,
        public array $teacherIds,
        public array $roomIds,
    ) {
    }

    /**
     * Nombre de créneaux occupés par chaque séance de cette requête.
     */
    public function durationSlots(int $slotStepMinutes): int
    {
        if ($this->subjectPlan->session_duration % $slotStepMinutes !== 0) {
            throw new \DomainException(sprintf(
                'La durée de session %d minutes n\'est pas compatible avec un pas de grille de %d minutes.',
                $this->subjectPlan->session_duration,
                $slotStepMinutes
            ));
        }

        return intdiv($this->subjectPlan->session_duration, $slotStepMinutes);
    }

    /**
     * Score de difficulté MRV : plus c'est contraint, plus c'est élevé.
     */
    public function computeDifficulty(int $slotStepMinutes): int
    {
        $durationSlots = $this->durationSlots($slotStepMinutes);

        $score = 0;
        $score += $durationSlots * $this->sessionsCount * 100;       // poids du volume
        $score += (count($this->roomIds) === 0 ? 1000 : (int) round(1000 / count($this->roomIds))); // rareté salles
        $score += (count($this->teacherIds) === 0 ? 1000 : (int) round(1000 / count($this->teacherIds))); // rareté enseignants
        $score += $this->subjectPlan->teaching_type === 'TP' ? 500 : 0; // TP plus contraint
        $score += ($this->groupNumber !== null ? 250 : 0);          // groupes séparés plus durs

        return $this->difficultyScore = $score;
    }
}
