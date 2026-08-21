<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AcademicSession;
use App\Models\ClassRoom;
use App\Models\Room;
use App\Models\SubjectPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

$sessions = AcademicSession::with(['classRoom', 'teacher', 'room', 'subjectPlan.subject'])
    ->orderBy('day')
    ->orderBy('start_time')
    ->get();

echo "Total sessions: {$sessions->count()}\n\n";

// Check conflicts
$conflicts = ['class' => 0, 'teacher' => 0, 'room' => 0];

for ($i = 0; $i < $sessions->count(); $i++) {
    for ($j = $i + 1; $j < $sessions->count(); $j++) {
        $a = $sessions[$i];
        $b = $sessions[$j];

        if ($a->day !== $b->day) continue;

        $overlap = $a->start_time < $b->end_time && $a->end_time > $b->start_time;
        if (!$overlap) continue;

        if ($a->class_room_id === $b->class_room_id) {
            $conflicts['class']++;
            echo "CLASS CONFLICT: {$a->classRoom->name} on {$a->day} {$a->start_time}-{$a->end_time} AND {$b->start_time}-{$b->end_time}\n";
        }
        if ($a->teacher_id === $b->teacher_id) {
            $conflicts['teacher']++;
            echo "TEACHER CONFLICT: {$a->teacher->name} on {$a->day}\n";
        }
        if ($a->room_id && $a->room_id === $b->room_id) {
            $conflicts['room']++;
            echo "ROOM CONFLICT: room {$a->room_id} on {$a->day}\n";
        }
    }
}

echo "\nConflict summary: class={$conflicts['class']}, teacher={$conflicts['teacher']}, room={$conflicts['room']}\n";

// Check teacher eligibility
$invalidTeachers = 0;
foreach ($sessions as $s) {
    $eligible = DB::table('teacher_subject')
        ->where('teacher_id', $s->teacher_id)
        ->where('subject_id', $s->subjectPlan->subject_id)
        ->exists();
    if (!$eligible) {
        $invalidTeachers++;
        echo "INVALID TEACHER: {$s->teacher->name} for {$s->subjectPlan->subject->name}\n";
    }
}

// Check room capacity
$capacityIssues = 0;
foreach ($sessions as $s) {
    if ($s->room && $s->classRoom) {
        $required = $s->subjectPlan->teaching_type === 'TP'
            ? (int) ceil($s->classRoom->student_count / config('timetable.tp_groups', 2))
            : $s->classRoom->student_count;
        if ($s->room->capacity < $required) {
            $capacityIssues++;
            echo "CAPACITY ISSUE: room {$s->room->name} ({$s->room->capacity}) < required {$required}\n";
        }
    }
}

echo "\nInvalid teachers: {$invalidTeachers}\n";
echo "Capacity issues: {$capacityIssues}\n";

// Teachers and rooms used
echo "\nTeachers used: " . $sessions->pluck('teacher_id')->unique()->count() . "\n";
echo "Rooms used: " . $sessions->pluck('room_id')->unique()->count() . "\n";
