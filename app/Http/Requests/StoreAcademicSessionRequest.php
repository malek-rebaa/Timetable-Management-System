<?php

namespace App\Http\Requests;

use App\Services\Timetable\ConflictChecker;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreAcademicSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation structurelles (indépendantes des conflits).
     */
    public function rules(): array
    {
        return [
            'subject_plan_id' => ['required', 'exists:subject_plans,id'],
            'teacher_id' => ['required', 'exists:users,id'],
            'class_room_id' => ['required', 'exists:class_rooms,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'day' => ['required', Rule::in(config('timetable.days'))],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'group_number' => ['nullable', 'integer', 'min:1', 'max:' . config('timetable.tp_groups')],
            'is_locked' => ['sometimes', 'boolean'],
            'timetable_id' => ['nullable', 'exists:timetables,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'end_time.after' => "L'heure de fin doit être après l'heure de début.",
            'day.in' => 'Le jour sélectionné est invalide.',
            'group_number.max' => 'Le groupe doit être 1 ou 2.',
        ];
    }

    /**
     * Après la validation structurelle, exécute le ConflictChecker métier.
     * Un conflit est transformé en erreur de validation.
     *
     * @throws ValidationException
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // On ne vérifie les conflits que si la structure est valide
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $candidate = new \App\Models\AcademicSession();
            $candidate->subject_plan_id = $this->input('subject_plan_id');
            $candidate->teacher_id = $this->input('teacher_id');
            $candidate->class_room_id = $this->input('class_room_id');
            $candidate->room_id = $this->input('room_id');
            $candidate->day = $this->input('day');
            $candidate->start_time = $this->input('start_time');
            $candidate->end_time = $this->input('end_time');
            $candidate->group_number = $this->input('group_number');
            $candidate->timetable_id = $this->input('timetable_id');
            $candidate->is_locked = (bool) $this->input('is_locked', false);
            $candidate->setRelation('subjectPlan', \App\Models\SubjectPlan::find($this->input('subject_plan_id')));
            $candidate->setRelation('classRoom', \App\Models\ClassRoom::find($this->input('class_room_id'))->loadMissing('level'));
            $candidate->setRelation('room', $this->input('room_id') ? \App\Models\Room::find($this->input('room_id')) : null);

            $ignoreId = $this->route('session') ? $this->route('session')->id : null;

            $checker = app(ConflictChecker::class);
            $errors = $checker->check($candidate, $ignoreId);

            foreach ($errors as $message) {
                $validator->errors()->add('conflict', $message);
            }
        });
    }
}
