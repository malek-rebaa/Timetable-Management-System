<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AcademicSession;
use App\Models\Timetable;
use App\Services\Timetable\TimetableGenerator;
use Illuminate\Support\Facades\DB;

// Clear previous test sessions (keep locked ones)
AcademicSession::where('is_locked', false)->delete();

echo "=== FRESH GENERATION ===\n";
$timetable = Timetable::create([
    'name' => 'Corrected Gen ' . now()->format('Y-m-d H:i:s'),
    'status' => 'RUNNING',
]);

$generator = app(TimetableGenerator::class);
$result = $generator->generate($timetable);

echo "Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
echo "Placed: {$result['placed']}\n";
echo "Unplaced: {$result['unplaced']}\n";
echo "Sessions in DB: " . AcademicSession::count() . "\n";
echo "Timetable status: " . $timetable->fresh()->status . "\n";

if (isset($result['diagnostics'])) {
    echo "\nDiagnostics:\n";
    foreach ($result['diagnostics'] as $k => $v) {
        echo "  {$k}: {$v}\n";
    }
}

if (!empty($result['errors'])) {
    echo "\nErrors:\n";
    foreach ($result['errors'] as $err) {
        echo "  - {$err}\n";
    }
}

// Validate
echo "\n=== VALIDATION ===\n";
$sessions = AcademicSession::with(['classRoom', 'teacher', 'room', 'subjectPlan.subject'])->get();
$conflicts = ['class' => 0, 'teacher' => 0, 'room' => 0];
$invalidTeachers = 0;

for ($i = 0; $i < $sessions->count(); $i++) {
    for ($j = $i + 1; $j < $sessions->count(); $j++) {
        $a = $sessions[$i]; $b = $sessions[$j];
        if ($a->day !== $b->day) continue;
        $overlap = $a->start_time < $b->end_time && $a->end_time > $b->start_time;
        if (!$overlap) continue;
        if ($a->class_room_id === $b->class_room_id) $conflicts['class']++;
        if ($a->teacher_id === $b->teacher_id) $conflicts['teacher']++;
        if ($a->room_id && $a->room_id === $b->room_id) $conflicts['room']++;
    }
}

foreach ($sessions as $s) {
    $eligible = DB::table('teacher_subject')
        ->where('teacher_id', $s->teacher_id)
        ->where('subject_id', $s->subjectPlan->subject_id)
        ->exists();
    if (!$eligible) $invalidTeachers++;
}

echo "Conflicts: class={$conflicts['class']}, teacher={$conflicts['teacher']}, room={$conflicts['room']}\n";
echo "Invalid teachers: {$invalidTeachers}\n";
echo "Teachers used: " . $sessions->pluck('teacher_id')->unique()->count() . "\n";
echo "Rooms used: " . $sessions->pluck('room_id')->unique()->count() . "\n";
