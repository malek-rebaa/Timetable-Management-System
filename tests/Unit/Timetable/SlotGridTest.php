<?php

namespace Tests\Unit\Timetable;

use App\Services\Timetable\SlotGrid;
use PHPUnit\Framework\TestCase;

class SlotGridTest extends TestCase
{
    public function test_it_builds_correct_number_of_slots()
    {
        $config = [
            'days' => ['MONDAY', 'TUESDAY'],
            'slot_step' => 30,
            'day_start' => '08:00',
            'day_end' => '12:00',
            'breaks' => [],
        ];

        $grid = new SlotGrid($config);
        
        // 4 hours * 2 (30 min) = 8 slots per day
        $this->assertEquals(8, $grid->slotsPerDay);
        $this->assertCount(8, $grid->slotsForDay('MONDAY'));
        $this->assertCount(8, $grid->slotsForDay('TUESDAY'));
    }

    public function test_it_skips_breaks()
    {
        $config = [
            'days' => ['MONDAY'],
            'slot_step' => 30,
            'day_start' => '08:00',
            'day_end' => '14:00', // 6 hours = 12 slots without break
            'breaks' => [
                ['start' => '12:00', 'end' => '13:00'] // 2 slots break
            ],
        ];

        $grid = new SlotGrid($config);
        
        $this->assertEquals(10, $grid->slotsPerDay);
        
        $slots = $grid->slotsForDay('MONDAY');
        $this->assertEquals('08:00', $slots[0]['start']);
        $this->assertEquals('11:30', $slots[7]['start']);
        $this->assertEquals('12:00', $slots[7]['end']); // just before break
        
        $this->assertEquals('13:00', $slots[8]['start']); // just after break
    }

    public function test_duration_to_slots()
    {
        $grid = new SlotGrid([
            'days' => ['MONDAY'],
            'slot_step' => 30,
            'day_start' => '08:00',
            'day_end' => '12:00',
        ]);

        $this->assertEquals(2, $grid->durationToSlots(60));
        $this->assertEquals(3, $grid->durationToSlots(90));
        $this->assertEquals(4, $grid->durationToSlots(120));
    }
}
