<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProntuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'created_by' => ['nullable', 'exists:users,id'],
        ];
    }
}
