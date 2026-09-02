<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $level = $this->route('level');

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('tenant.levels', 'name')->ignore($level)],
        ];
    }
}
