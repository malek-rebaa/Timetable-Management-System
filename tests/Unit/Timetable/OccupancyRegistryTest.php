<?php

namespace Tests\Unit\Timetable;

use App\Services\Timetable\OccupancyRegistry;
use App\Services\Timetable\SlotGrid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OccupancyRegistryTest extends TestCase
{
    use RefreshDatabase;

    protected SlotGrid $grid;
    protected OccupancyRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Use default config
        $this->grid = new SlotGrid();
        $this->registry = new OccupancyRegistry($this->grid);
    }

    /**
     * TEST 1: THEORY session should block all TP groups
     */
    public function test_theory_session_blocks_tp_groups()
    {
        $classId = 1;
        $teacherId = 1;
        $roomId = 1;
        $day = 'MONDAY';
        $slotIndex = 0;
        $slots = 3; // 90 minutes with 30-min slots

        // Book a THEORY session (groupNumber = null)
        $this->registry->book($teacherId, $roomId, $classId, null, $day, $slotIndex, $slots);

        // Try to place TP group 1 - should fail
        $this->assertFalse(
            $this->registry->isClassFree($classId, 1, $day, $slotIndex, $slots),
            'TP Group 1 should not be free when THEORY session is booked'
        );

        // Try to place TP group 2 - should also fail
        $this->assertFalse(
            $this->registry->isClassFree($classId, 2, $day, $slotIndex, $slots),
            'TP Group 2 should not be free when THEORY session is booked'
        );
    }

    /**
     * TEST 2: TP groups should block THEORY sessions
     */
    public function test_tp_groups_block_theory_sessions()
    {
        $classId = 1;
        $teacherId = 1;
        $roomId = 1;
        $day = 'MONDAY';
        $slotIndex = 0;
        $slots = 4; // 120 minutes

        // Book TP group 1
        $this->registry->book($teacherId, $roomId, $classId, 1, $day, $slotIndex, $slots);

        // Try to place THEORY session - should fail
        $this->assertFalse(
            $this->registry->isClassFree($classId, null, $day, $slotIndex, $slots),
            'THEORY session should not be free when TP group is booked'
        );
    }

    /**
     * TEST 3: Different TP groups can coexist if not overlapping
     */
    public function test_different_tp_groups_can_coexist_when_not_overlapping()
    {
        $classId = 1;
        $teacherId1 = 1;
        $teacherId2 = 2;
        $roomId1 = 1;
        $roomId2 = 2;
        $day = 'MONDAY';
        
        // Book group 1 at slot 0-3 (90 min)
        $this->registry->book($teacherId1, $roomId1, $classId, 1, $day, 0, 3);

        // Group 2 at slot 3-6 should be free (contiguous but not overlapping)
        $this->assertTrue(
            $this->registry->isClassFree($classId, 2, $day, 3, 3),
            'TP Group 2 should be free at non-overlapping time'
        );
    }

    /**
     * TEST 4: Same TP groups cannot overlap
     */
    public function test_same_tp_groups_cannot_overlap()
    {
        $classId = 1;
        $teacherId1 = 1;
        $teacherId2 = 2;
        $roomId1 = 1;
        $roomId2 = 2;
        $day = 'MONDAY';
        $slotIndex = 0;
        $slots = 3;

        // Book group 1
        $this->registry->book($teacherId1, $roomId1, $classId, 1, $day, $slotIndex, $slots);

        // Try to book group 1 again at same time - should fail
        $this->assertFalse(
            $this->registry->isClassFree($classId, 1, $day, $slotIndex, $slots),
            'Same TP group should not be free at overlapping time'
        );
    }

    public function test_tp_groups_cannot_overlap_by_default()
    {
        $this->registry->book(1, 1, 1, 1, 'MONDAY', 0, 3);

        $this->assertFalse($this->registry->isClassFree(1, 2, 'MONDAY', 0, 3));
    }

    /**
     * TEST 5: Teacher conflict detection
     */
    public function test_teacher_conflict_detection()
    {
        $teacherId = 1;
        $day = 'MONDAY';
        $slotIndex = 0;
        $slots = 3;

        // Book teacher
        $this->registry->book($teacherId, 1, 1, null, $day, $slotIndex, $slots);

        // Check teacher is busy
        $this->assertFalse(
            $this->registry->isTeacherFree($teacherId, $day, $slotIndex, $slots),
            'Teacher should not be free at booked time'
        );

        // Check teacher is free at different time
        $this->assertTrue(
            $this->registry->isTeacherFree($teacherId, $day, 5, $slots),
            'Teacher should be free at non-overlapping time'
        );
    }

    /**
     * TEST 6: Room conflict detection
     */
    public function test_room_conflict_detection()
    {
        $roomId = 1;
        $day = 'MONDAY';
        $slotIndex = 0;
        $slots = 3;

        // Book room
        $this->registry->book(1, $roomId, 1, null, $day, $slotIndex, $slots);

        // Check room is busy
        $this->assertFalse(
            $this->registry->isRoomFree($roomId, $day, $slotIndex, $slots),
            'Room should not be free at booked time'
        );

        // Check room is free at different time
        $this->assertTrue(
            $this->registry->isRoomFree($roomId, $day, 5, $slots),
            'Room should be free at non-overlapping time'
        );
    }

    /**
     * TEST 7: Adjacent sessions (no gap) should be allowed
     */
    public function test_adjacent_sessions_are_allowed()
    {
        $classId = 1;
        $teacherId1 = 1;
        $teacherId2 = 2;
        $roomId1 = 1;
        $roomId2 = 2;
        $day = 'MONDAY';

        // Book first session at slot 0-2 (60 min)
        $this->registry->book($teacherId1, $roomId1, $classId, null, $day, 0, 2);

        // Second session at slot 2-4 should be allowed (adjacent, not overlapping)
        $this->assertTrue(
            $this->registry->isClassFree($classId, null, $day, 2, 2),
            'Adjacent session should be allowed'
        );

        $this->assertTrue(
            $this->registry->isTeacherFree($teacherId2, $day, 2, 2),
            'Different teacher should be free at adjacent time'
        );

        $this->assertTrue(
            $this->registry->isRoomFree($roomId2, $day, 2, 2),
            'Different room should be free at adjacent time'
        );
    }

    /**
     * TEST 8: Overlapping sessions should be rejected
     */
    public function test_overlapping_sessions_are_rejected()
    {
        $classId = 1;
        $teacherId = 1;
        $roomId = 1;
        $day = 'MONDAY';

        // Book session from slot 0-3 (90 min)
        $this->registry->book($teacherId, $roomId, $classId, null, $day, 0, 3);

        // Try overlapping session from slot 2-5
        $this->assertFalse(
            $this->registry->isClassFree($classId, null, $day, 2, 3),
            'Overlapping class session should be rejected'
        );

        $this->assertFalse(
            $this->registry->isTeacherFree($teacherId, $day, 2, 3),
            'Overlapping teacher session should be rejected'
        );

        $this->assertFalse(
            $this->registry->isRoomFree($roomId, $day, 2, 3),
            'Overlapping room session should be rejected'
        );
    }

    /**
     * TEST 9: Multiple groups booking tracking
     */
    public function test_multiple_groups_booking_tracking()
    {
        $classId = 1;
        $day = 'MONDAY';

        // Book group 1
        $this->registry->book(1, 1, $classId, 1, $day, 0, 3);
        
        // Book group 2 at different time
        $this->registry->book(2, 2, $classId, 2, $day, 3, 3);

        // THEORY should fail at both times
        $this->assertFalse(
            $this->registry->isClassFree($classId, null, $day, 0, 3),
            'THEORY should fail when group 1 is booked'
        );

        $this->assertFalse(
            $this->registry->isClassFree($classId, null, $day, 3, 3),
            'THEORY should fail when group 2 is booked'
        );

        // But THEORY at slot 6 should work (after both groups)
        $this->assertTrue(
            $this->registry->isClassFree($classId, null, $day, 6, 3),
            'THEORY should be free after all groups are done'
        );
    }

    /**
     * TEST 10: Teacher load calculation
     */
    public function test_teacher_load_calculation()
    {
        $teacherId = 1;
        $day1 = 'MONDAY';
        $day2 = 'TUESDAY';

        // Book 3 slots on Monday
        $this->registry->book($teacherId, 1, 1, null, $day1, 0, 3);
        
        // Book 4 slots on Tuesday
        $this->registry->book($teacherId, 2, 2, null, $day2, 0, 4);

        // Total load should be 7 slots
        $this->assertEquals(
            7,
            $this->registry->teacherLoad($teacherId),
            'Teacher load should be sum of all bookings'
        );
    }
}
