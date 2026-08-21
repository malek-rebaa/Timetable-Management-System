<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SubjectPlan;
use Illuminate\Support\Facades\DB;

$plans = SubjectPlan::with('subject')->get();

foreach ($plans as $plan) {
    $subjectName = $plan->subject->name;
    $subjectId = $plan->subject_id;

    // Direct SQL
    $directIds = DB::table('teacher_subject')
        ->where('subject_id', $subjectId)
        ->pluck('teacher_id')
        ->toArray();

    // Via relationship
    $relIds = $plan->teachers()->pluck('users.id')->toArray();
    $relIds2 = $plan->teachers()->pluck('teacher_id')->toArray();
    $relIds3 = $plan->teachers->pluck('id')->toArray();

    echo "{$subjectName} (subject_id={$subjectId}):\n";
    echo "  Direct SQL: [" . implode(',', $directIds) . "]\n";
    echo "  pluck(users.id): [" . implode(',', $relIds) . "]\n";
    echo "  pluck(teacher_id): [" . implode(',', $relIds2) . "]\n";
    echo "  collection pluck(id): [" . implode(',', $relIds3) . "]\n";

    // Show the actual SQL
    $sql = $plan->teachers()->toSql();
    $bindings = $plan->teachers()->getBindings();
    echo "  SQL: {$sql}\n";
    echo "  Bindings: " . json_encode($bindings) . "\n\n";
}
