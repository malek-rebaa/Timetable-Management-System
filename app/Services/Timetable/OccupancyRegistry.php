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

    /** @var array<int, array<string, int>> classId => day => bitmask */
    protected array $classBusy = [];

    /** @var array<int, array<string, int>> teacherId => nombre de créneaux occupés (charge) */
    protected array $teacherLoad = [];

    public function __construct(protected SlotGrid $grid)
    {
    }

    /**
     * Marque une occupation pour les 3 ressources.
     */
    public function book(int $teacherId, int $roomId, int $classId, string $day, int $startIndex, int $slots): void
    {
        $mask = $this->spanMask($startIndex, $slots);

        $this->teacherBusy[$teacherId][$day] = ($this->teacherBusy[$teacherId][$day] ?? 0) | $mask;
        $this->roomBusy[$roomId][$day] = ($this->roomBusy[$roomId][$day] ?? 0) | $mask;
        $this->classBusy[$classId][$day] = ($this->classBusy[$classId][$day] ?? 0) | $mask;

        $this->teacherLoad[$teacherId] = ($this->teacherLoad[$teacherId] ?? 0) + $slots;
    }

    /**
     * Retire une occupation (pour la réparation / backtracking).
     */
    public function unbook(int $teacherId, int $roomId, int $classId, string $day, int $startIndex, int $slots): void
    {
        $mask = $this->spanMask($startIndex, $slots);
        $clear = ~$mask;

        $this->teacherBusy[$teacherId][$day] = ($this->teacherBusy[$teacherId][$day] ?? 0) & $clear;
        $this->roomBusy[$roomId][$day] = ($this->roomBusy[$roomId][$day] ?? 0) & $clear;
        $this->classBusy[$classId][$day] = ($this->classBusy[$classId][$day] ?? 0) & $clear;

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

    public function isClassFree(int $classId, string $day, int $startIndex, int $slots): bool
    {
        return $this->isFree($this->classBusy[$classId][$day] ?? 0, $startIndex, $slots);
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
}
