<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

$ts = DB::table('teacher_subject')->get();
echo "teacher_subject entries: {$ts->count()}\n";
foreach ($ts as $t) {
    echo "  teacher={$t->teacher_id} subject={$t->subject_id}\n";
}

$teachers = User::where('role', 'TEACHER')->get();
foreach ($teachers as $t) {
    $subs = $t->subjects()->pluck('name')->join(', ');
    echo "Teacher {$t->id} ({$t->name}): {$subs}\n";
}

// Check what RequestBuilder returns
echo "\n=== RequestBuilder output ===\n";
$builder = app(\App\Services\Timetable\RequestBuilder::class);
$requests = $builder->build();
foreach ($requests as $r) {
    $subject = $r->subjectPlan->subject->name;
    $class = $r->classRoom->name;
    $teachers = implode(',', $r->teacherIds);
    $rooms = count($r->roomIds);
    echo "{$class} - {$subject} ({$r->subjectPlan->teaching_type}) teachers=[{$teachers}] rooms={$rooms} sessions={$r->sessionsCount}\n";
}
