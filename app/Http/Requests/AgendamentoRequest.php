<?php

namespace App\Http\Requests;

use App\Models\Paciente;
use App\Models\Profissional;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgendamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paciente_id' => ['required', Rule::exists((new Paciente())->getTable(), 'id')],
            'profissional_id' => ['nullable', Rule::exists((new Profissional())->getTable(), 'id')],
            'scheduled_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'valor_sessao' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
