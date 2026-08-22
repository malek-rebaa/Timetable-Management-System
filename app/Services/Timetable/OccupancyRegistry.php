<?php

namespace App\Services\Timetable;

/**
 * Registre d'occupation en mémoire pendant la génération.
 *
 * Contient l'état des 3 ressources partagées (enseignant, salle, classe)
 * pour chaque jour. Aucune requête SQL : tout vit dans des bitmasks.
 *
 * - 1 bit à 1 = créneau occupé
 * - slotsPerDay <= 64 → chaque journée tient dans un entier 64 bits
 */
class OccupancyRegistry
{
    /** @var array<int, array<string, int>> teacherId => day => bitmask */
    protected array $teacherBusy = [];

    /** @var array<int, array<string, int>> roomId => day => bitmask */
    protected array $roomBusy = [];

    /** @var array<int, array<int|string, array<string, int>>> classId => groupNumber|'ALL' => day => bitmask */
    protected array $classBusy = [];

    /** @var array<int, array<string, int>> teacherId => nombre de créneaux occupés (charge) */
    protected array $teacherLoad = [];

    public function __construct(protected SlotGrid $grid)
    {
    }

    /**
     * Marque une occupation pour les 3 ressources.
     */
    public function book(int $teacherId, ?int $roomId, int $classId, ?int $groupNumber, string $day, int $startIndex, int $slots): void
    {
        $mask = $this->spanMask($startIndex, $slots);

        $this->teacherBusy[$teacherId][$day] = ($this->teacherBusy[$teacherId][$day] ?? 0) | $mask;
        if ($roomId !== null) {
            $this->roomBusy[$roomId][$day] = ($this->roomBusy[$roomId][$day] ?? 0) | $mask;
        }
        
        $groupKey = $groupNumber ?? 'ALL';
        $this->classBusy[$classId][$groupKey][$day] = ($this->classBusy[$classId][$groupKey][$day] ?? 0) | $mask;

        $this->teacherLoad[$teacherId] = ($this->teacherLoad[$teacherId] ?? 0) + $slots;
    }

    /**
     * Retire une occupation (pour la réparation / backtracking).
     */
    public function unbook(int $teacherId, ?int $roomId, int $classId, ?int $groupNumber, string $day, int $startIndex, int $slots): void
    {
        $mask = $this->spanMask($startIndex, $slots);
        $clear = ~$mask;

        $this->teacherBusy[$teacherId][$day] = ($this->teacherBusy[$teacherId][$day] ?? 0) & $clear;
        if ($roomId !== null) {
            $this->roomBusy[$roomId][$day] = ($this->roomBusy[$roomId][$day] ?? 0) & $clear;
        }
        
        $groupKey = $groupNumber ?? 'ALL';
        $this->classBusy[$classId][$groupKey][$day] = ($this->classBusy[$classId][$groupKey][$day] ?? 0) & $clear;

        $this->teacherLoad[$teacherId] = max(0, ($this->teacherLoad[$teacherId] ?? 0) - $slots);
    }

    public function isTeacherFree(int $teacherId, string $day, int $startIndex, int $slots): bool
    {
        return $this->isFree($this->teacherBusy[$teacherId][$day] ?? 0, $startIndex, $slots);
    }

    public function isRoomFree(int $roomId, string $day, int $startIndex, int $slots): bool
    {
        return $this->isFree($this->roomBusy[$roomId][$day] ?? 0, $startIndex, $slots);
    }

    public function isClassFree(int $classId, ?int $groupNumber, string $day, int $startIndex, int $slots): bool
    {
        // 1. Si on veut placer un groupe spécifique, vérifier que la classe entière n'est pas occupée
        if ($groupNumber !== null) {
            // Vérifier d'abord si la classe entière (THEORY) est occupée
            if (!$this->isFree($this->classBusy[$classId]['ALL'][$day] ?? 0, $startIndex, $slots)) {
                return false;
            }
            // Puis vérifier ce groupe spécifique
            if (! config('timetable.parallel_tp_groups', false)) {
                foreach ($this->classBusy[$classId] ?? [] as $days) {
                    if (! $this->isFree($days[$day] ?? 0, $startIndex, $slots)) {
                        return false;
                    }
                }

                return true;
            }

            return $this->isFree($this->classBusy[$classId][$groupNumber][$day] ?? 0, $startIndex, $slots);
        }

        // 2. Demande pour la classe entière (THEORY) : il faut qu'AUCUN groupe ne soit occupé
        // ET que la classe entière ne soit pas déjà occupée
        if (!$this->isFree($this->classBusy[$classId]['ALL'][$day] ?? 0, $startIndex, $slots)) {
            return false;
        }

        foreach ($this->classBusy[$classId] ?? [] as $group => $days) {
            if ($group !== 'ALL') {
                if (!$this->isFree($days[$day] ?? 0, $startIndex, $slots)) {
                    return false;
                }
            }
        }
        
        return true;
    }

    /**
     * Charge hebdomadaire d'un enseignant (en créneaux).
     */
    public function teacherLoad(int $teacherId): int
    {
        return $this->teacherLoad[$teacherId] ?? 0;
    }

    protected function isFree(int $busyMask, int $startIndex, int $slots): bool
    {
        // Les bits [startIndex, startIndex+slots) doivent être tous à 0
        return ($busyMask & $this->spanMask($startIndex, $slots)) === 0;
    }

    /**
     * Construit un masque avec `slots` bits à 1 à partir de `startIndex`.
     */
    protected function spanMask(int $startIndex, int $slots): int
    {
        // Slots <= 64 : on reste dans un entier 64 bits (int PHP = 64 bits sous Windows x64)
        $bits = (1 << $slots) - 1;

        return $bits << $startIndex;
    }

    /**
     * Nombre total de créneaux valides par jour (utile pour les tests).
     */
    public function slotsPerDay(): int
    {
        return $this->grid->slotsPerDay;
    }

    /**
     * Nombre de séances (créneaux occupés) placées sur un jour donné.
     * Somme sur toutes les ressources (enseignants, salles, classes).
     */
    public function sessionsCountOnDay(string $day): int
    {
        $count = 0;
        
        // Compter les créneaux occupés pour les enseignants
        foreach ($this->teacherBusy as $teacherDays) {
            if (isset($teacherDays[$day])) {
                $count += $this->countBits($teacherDays[$day]);
            }
        }
        
        // Compter les créneaux occupés pour les salles
        foreach ($this->roomBusy as $roomDays) {
            if (isset($roomDays[$day])) {
                $count += $this->countBits($roomDays[$day]);
            }
        }
        
        // Compter les créneaux occupés pour les classes
        foreach ($this->classBusy as $classGroups) {
            foreach ($classGroups as $groupDays) {
                if (isset($groupDays[$day])) {
                    $count += $this->countBits($groupDays[$day]);
                }
            }
        }
        
        return $count;
    }

    /**
     * Compte le nombre de bits à 1 dans un entier (population count).
     */
    protected function countBits(int $mask): int
    {
        $count = 0;
        while ($mask) {
            $count += $mask & 1;
            $mask >>= 1;
        }
        return $count;
    }
}
