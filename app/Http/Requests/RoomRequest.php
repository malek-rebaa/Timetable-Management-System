<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $room = $this->route('room');

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('tenant.rooms', 'name')->ignore($room)],
            'type' => ['required', Rule::in(['CLASSROOM', 'LABORATORY', 'AMPHITHEATER'])],
            'capacity' => ['required', 'integer', 'min:1', 'max:5000'],
        ];
    }
}
