<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClassRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $classRoom = $this->route('classRoom');

        return [
            'level_id' => ['required', 'exists:levels,id'],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('class_rooms', 'name')
                    ->where(fn ($query) => $query->where('level_id', $this->input('level_id')))
                    ->ignore($classRoom),
            ],
            'student_count' => ['required', 'integer', 'min:1', 'max:1000'],
        ];
    }
}
