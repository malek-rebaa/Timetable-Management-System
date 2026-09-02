<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubjectPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $subjectPlan = $this->route('subjectPlan');

        return [
            'level_id' => ['required', 'exists:tenant.levels,id'],
            'subject_id' => ['required', 'exists:tenant.subjects,id'],
            'sessions_per_week' => ['required', 'integer', 'min:1', 'max:20'],
            'session_duration' => ['required', 'integer', 'min:30', 'max:480'],
            'teaching_type' => ['required', Rule::in(['THEORY', 'TP'])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $duration = (int) $this->input('session_duration');
            $step = (int) config('timetable.slot_step');

            if ($duration > 0 && $duration % $step !== 0) {
                $validator->errors()->add('session_duration', "La durée doit être un multiple de {$step} minutes.");
            }

            $query = \App\Models\SubjectPlan::query()
                ->where('level_id', $this->input('level_id'))
                ->where('subject_id', $this->input('subject_id'))
                ->where('teaching_type', $this->input('teaching_type'));

            if ($subjectPlan = $this->route('subjectPlan')) {
                $query->whereKeyNot($subjectPlan);
            }

            if ($query->exists()) {
                $validator->errors()->add('subject_id', 'Ce programme existe déjà pour ce niveau et ce type d’enseignement.');
            }
        });
    }
}
