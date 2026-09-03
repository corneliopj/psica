<?php

namespace App\Http\Requests;

use App\Models\Paciente;
use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ProntuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paciente_id' => ['required', Rule::exists((new Paciente())->getTable(), 'id')],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'created_by' => [
                'nullable',
                Rule::exists((new Usuario())->getTable(), 'id')->when(
                    Schema::hasColumn('prontuarios', 'created_by'),
                    fn ($rule) => $rule
                ),
            ],
        ];
    }
}
