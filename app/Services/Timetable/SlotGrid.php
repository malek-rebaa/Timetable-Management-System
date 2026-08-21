<?php

namespace App\Services\Timetable;

use Carbon\Carbon;

/**
 * Grille de temps hebdomadaire discrète.
 *
 * La grille est construite à partir de config/timetable.php et ne contient
 * QUE des créneaux valides (jours ouvrés, plage horaire, hors pauses).
 * Une séance occupe un nombre entier de créneaux contigus.
 */
class SlotGrid
{
    /** @var array<int, string> Jours ouvrés, ex. ['MONDAY', …] */
    public array $days;

    /** @var int Durée d'un créneau en minutes */
    public int $slotStep;

    /** @var string Heure de début de journée "H:i" */
    public string $dayStart;

    /** @var string Heure de fin de journée "H:i" */
    public string $dayEnd;

    /** @var array<int, array{start: string, end: string}> Pauses */
    public array $breaks;

    public int $slotsPerDay;

    /** @var array<string, array<int, array{start: string, end: string}>> day => [ index => Slot ] */
    protected array $slots = [];

    public function __construct(?array $config = null)
    {
        $config ??= config('timetable');

        $this->days = $config['days'];
        $this->slotStep = (int) $config['slot_step'];
        $this->dayStart = $config['day_start'];
        $this->dayEnd = $config['day_end'];
        $this->breaks = $config['breaks'] ?? [];

        $this->build();
    }

    protected function build(): void
    {
        $start = Carbon::parse($this->dayStart);
        $end = Carbon::parse($this->dayEnd);
        $cursor = $start->copy();

        $index = 0;
        while ($cursor->lessThan($end)) {
            $slotEnd = $cursor->copy()->addMinutes($this->slotStep);

            if ($slotEnd->greaterThan($end)) {
                break;
            }

            // Si le créneau tombe dans une pause, on le saute
            if (! $this->isInBreak($cursor, $slotEnd)) {
                foreach ($this->days as $day) {
                    $this->slots[$day][$index] = [
                        'start' => $cursor->format('H:i'),
                        'end' => $slotEnd->format('H:i'),
                    ];
                }
                $index++;
            }

            $cursor->addMinutes($this->slotStep);
        }

        $this->slotsPerDay = $index;
    }

    protected function isInBreak(Carbon $start, Carbon $end): bool
    {
        foreach ($this->breaks as $break) {
            $bStart = Carbon::parse($break['start']);
            $bEnd = Carbon::parse($break['end']);

            // Chevauchement entre [start, end) et [bStart, bEnd)
            if ($start->lessThan($bEnd) && $end->greaterThan($bStart)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Nombre de créneaux nécessaires pour une durée donnée.
     */
    public function durationToSlots(int $minutes): int
    {
        if ($minutes <= 0 || $minutes % $this->slotStep !== 0) {
            return 0;
        }

        return intdiv($minutes, $this->slotStep);
    }

    /**
     * Index de début d'une heure donnée, ou null si le créneau est invalide
     * (pause, hors plage).
     */
    public function indexOf(string $day, string $time): ?int
    {
        $time = $this->normalizeTime($time);

        foreach ($this->slots[$day] ?? [] as $index => $slot) {
            if ($slot['start'] === $time) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Liste des créneaux valides pour un jour donné.
     *
     * @return array<int, array{start: string, end: string}>
     */
    public function slotsForDay(string $day): array
    {
        return $this->slots[$day] ?? [];
    }

    /**
     * Teste si une séance [start, end) sur un jour est entièrement alignée
     * sur une suite de créneaux contigus de la grille.
     */
    public function isValidSpan(string $day, string $startTime, string $endTime, int $durationMinutes): bool
    {
        $startIndex = $this->indexOf($day, $startTime);

        if ($startIndex === null) {
            return false;
        }

        $needed = $this->durationToSlots($durationMinutes);

        if ($needed === 0) {
            return false;
        }

        $slots = $this->slotsForDay($day);

        // Le dernier créneau doit se terminer exactement à endTime
        $lastIndex = $startIndex + $needed - 1;
        if (! isset($slots[$lastIndex])) {
            return false;
        }

        return $this->isContiguous($day, $startIndex, $needed)
            && $slots[$lastIndex]['end'] === $this->normalizeTime($endTime);
    }

    public function slotStart(string $day, int $index): ?string
    {
        return $this->slots[$day][$index]['start'] ?? null;
    }

    public function slotEnd(string $day, int $index): ?string
    {
        return $this->slots[$day][$index]['end'] ?? null;
    }

    /**
     * Vérifie qu'une séquence de créneaux est temporellement continue.
     * (Aucun créneau ne traverse une pause de manière invisible).
     */
    public function isContiguous(string $day, int $startIndex, int $slotsNeeded): bool
    {
        if ($slotsNeeded <= 1) {
            return true;
        }

        $slots = $this->slotsForDay($day);

        for ($i = $startIndex; $i < $startIndex + $slotsNeeded - 1; $i++) {
            if (!isset($slots[$i]) || !isset($slots[$i + 1])) {
                return false;
            }
            if ($slots[$i]['end'] !== $slots[$i + 1]['start']) {
                return false;
            }
        }

        return true;
    }

    protected function normalizeTime(string $time): string
    {
        return Carbon::parse($time)->format('H:i');
    }
}
