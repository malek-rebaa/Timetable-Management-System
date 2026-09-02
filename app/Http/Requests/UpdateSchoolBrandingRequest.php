<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolBrandingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'SUPER_ADMIN'
            || $this->user()?->hasSystemRole('SUPER_ADMIN');
    }

    public function rules(): array
    {
        return [
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'primary_color.regex' => 'La couleur principale doit être au format hexadécimal, par exemple #2563EB.',
            'secondary_color.regex' => 'La couleur secondaire doit être au format hexadécimal, par exemple #1D4ED8.',
        ];
    }
}
