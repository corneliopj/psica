<?php

namespace App\Http\Requests;

use App\Models\Agendamento;
use App\Models\Paciente;
use App\Models\Profissional;
use Illuminate\Foundation\Http\FormRequest;
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
            'agendamento_id' => ['required', Rule::exists((new Agendamento())->getTable(), 'id')],
            'paciente_id' => ['required', Rule::exists((new Paciente())->getTable(), 'id')],
            'profissional_id' => ['required', Rule::exists((new Profissional())->getTable(), 'id')],
            'anotacoes' => ['nullable', 'string'],
            'historico_clinico' => ['nullable', 'string'],
        ];
    }
}
