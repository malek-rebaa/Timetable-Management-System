<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AcademicSession;
use Illuminate\Support\Facades\DB;

$sessions = AcademicSession::with(['classRoom', 'teacher', 'subjectPlan.subject'])->get();

foreach ($sessions as $s) {
    $subjectId = $s->subjectPlan->subject_id;
    $eligible = DB::table('teacher_subject')
        ->where('teacher_id', $s->teacher_id)
        ->where('subject_id', $subjectId)
        ->exists();

    $teacherName = $s->teacher?->name ?? 'NULL';
    $subjectName = $s->subjectPlan->subject->name;
    $status = $eligible ? 'OK' : 'INVALID';

    if (!$eligible) {
        echo "[{$status}] teacher_id={$s->teacher_id} ({$teacherName}) -> {$subjectName} (subject_id={$subjectId})\n";
    }
}

// Count by teacher
echo "\n=== Sessions by teacher ===\n";
$byTeacher = $sessions->groupBy('teacher_id');
foreach ($byTeacher as $tid => $group) {
    $name = $group->first()->teacher?->name ?? 'NULL';
    echo "Teacher {$tid} ({$name}): {$group->count()} sessions\n";
}
