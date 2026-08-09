<?php

namespace Tests\Unit\Timetable;

use App\Services\Timetable\OccupancyRegistry;
use App\Services\Timetable\SlotGrid;
use PHPUnit\Framework\TestCase;

class MemoryOccupancyTest extends TestCase
{
    protected SlotGrid $grid;

    protected function setUp(): void
    {
        parent::setUp();
        // Grid fictive pour tests (8 slots de 30 mins)
        $this->grid = new SlotGrid([
            'days' => ['MONDAY', 'TUESDAY'],
            'slot_step' => 30,
            'day_start' => '08:00',
            'day_end' => '12:00',
        ]);
    }

    public function test_it_books_and_checks_availability()
    {
        $registry = new OccupancyRegistry($this->grid);

        // Au départ tout est libre
        $this->assertTrue($registry->isTeacherFree(1, 'MONDAY', 0, 4)); // 4 slots = 2 heures

        // On réserve 4 créneaux (index 0 à 3) pour le prof 1
        $registry->book(1, 10, 100, 'MONDAY', 0, 4);

        // Le prof n'est plus libre sur ces créneaux
        $this->assertFalse($registry->isTeacherFree(1, 'MONDAY', 0, 4));
        
        // Mais libre après
        $this->assertTrue($registry->isTeacherFree(1, 'MONDAY', 4, 2));

        // Les salles et classes sont aussi réservées
        $this->assertFalse($registry->isRoomFree(10, 'MONDAY', 0, 4));
        $this->assertFalse($registry->isClassFree(100, 'MONDAY', 0, 4));

        // Mais libres les autres jours
        $this->assertTrue($registry->isTeacherFree(1, 'TUESDAY', 0, 4));
    }

    public function test_it_unbooks()
    {
        $registry = new OccupancyRegistry($this->grid);

        $registry->book(1, 10, 100, 'MONDAY', 2, 2);
        $this->assertFalse($registry->isTeacherFree(1, 'MONDAY', 2, 2));

        $registry->unbook(1, 10, 100, 'MONDAY', 2, 2);
        $this->assertTrue($registry->isTeacherFree(1, 'MONDAY', 2, 2));
    }
}
