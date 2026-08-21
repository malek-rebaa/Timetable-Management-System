<?php

namespace App\Jobs;

use App\Events\TimetableGenerated;
use App\Events\TimetableGenerationFailed;
use App\Models\Timetable;
use App\Services\Timetable\TimetableGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateTimetableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes max

    public function __construct(
        public ?int $timetableId = null,
        public array $options = []
    ) {
    }

    public function handle(TimetableGenerator $generator): void
    {
        $timetable = $this->timetableId
            ? Timetable::find($this->timetableId)
            : Timetable::create([
                'name' => $this->options['name'] ?? 'Generated ' . now()->format('Y-m-d H:i'),
                'academic_year' => $this->options['academic_year'] ?? null,
                'semester' => $this->options['semester'] ?? null,
                'status' => 'RUNNING',
            ]);

        if (! $timetable) {
            Log::warning('Timetable generation skipped because the timetable no longer exists.', [
                'timetable_id' => $this->timetableId,
            ]);

            return;
        }

        // Mettre à jour le statut
        $timetable->status = 'RUNNING';
        $timetable->save();

        try {
            if (isset($this->options['class_room_id'])) {
                $this->options['class_room_ids'] = [(int) $this->options['class_room_id']];
            }

            $result = $generator->generate($timetable, $this->options);

            if (isset($result['success']) && $result['success']) {
                $timetable->status = 'COMPLETED';
                $timetable->save();

                event(new TimetableGenerated($timetable, $result));
            } else {
                $timetable->status = 'FAILED';
                $timetable->save();

                event(new TimetableGenerationFailed($timetable, $result['errors'] ?? ['Génération échouée']));
            }
        } catch (\Throwable $e) {
            $timetable->status = 'FAILED';
            $timetable->save();

            event(new TimetableGenerationFailed($timetable, [$e->getMessage()]));

            throw $e;
        }
    }
}
