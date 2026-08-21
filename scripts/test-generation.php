<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AcademicSession;
use App\Models\ClassRoom;
use App\Models\Room;
use App\Models\SubjectPlan;
use App\Models\Timetable;
use App\Models\User;
use App\Services\Timetable\TimetableGenerator;

echo "=== DATABASE STATE ===\n";
echo "Classes: " . ClassRoom::count() . "\n";
echo "Plans: " . SubjectPlan::count() . "\n";
echo "Rooms: " . Room::count() . "\n";
echo "Teachers: " . User::where('role', 'TEACHER')->count() . "\n";
echo "Sessions (before): " . AcademicSession::count() . "\n";

$classes = ClassRoom::with('level')->get();
foreach ($classes as $c) {
    echo "  - {$c->name} (level={$c->level_id}, students={$c->student_count})\n";
}

$plans = SubjectPlan::with('subject')->get();
foreach ($plans as $p) {
    echo "  - {$p->subject->name} L{$p->level_id} {$p->teaching_type} {$p->sessions_per_week}x{$p->session_duration}min\n";
}

echo "\n=== GENERATION ===\n";
$timetable = Timetable::create([
    'name' => 'Test Real Gen ' . now()->format('Y-m-d H:i:s'),
    'status' => 'RUNNING',
]);

$generator = app(TimetableGenerator::class);
$result = $generator->generate($timetable);

echo "Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
echo "Placed: {$result['placed']}\n";
echo "Unplaced: {$result['unplaced']}\n";
echo "Sessions (after): " . AcademicSession::count() . "\n";
echo "Timetable status: " . $timetable->fresh()->status . "\n";

if (!empty($result['errors'])) {
    echo "\nErrors:\n";
    foreach ($result['errors'] as $err) {
        echo "  - {$err}\n";
    }
}

if (isset($result['diagnostics'])) {
    echo "\nDiagnostics:\n";
    print_r($result['diagnostics']);
}
