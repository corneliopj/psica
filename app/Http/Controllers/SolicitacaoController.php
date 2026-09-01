<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paciente;
use App\Models\Agendamento;

class SolicitacaoController extends Controller
{
    public function create()
    {
        return view('solicitar');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'scheduled_at' => 'required|date',
        ]);

        // Normalize phone (simple)
        $phone = preg_replace('/[^0-9+]/', '', $data['phone']);

        // Find or create paciente by phone
        $paciente = Paciente::firstOrCreate(
            ['phone' => $phone],
            ['name' => $data['name'], 'email' => null]
        );

        $scheduled = \Carbon\Carbon::parse($data['scheduled_at']);

        // Check if slot is free (exact timestamp)
        $exists = Agendamento::where('scheduled_at', $scheduled->toDateTimeString())->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['scheduled_at' => 'Horário já reservado. Por favor, escolha outro horário.']);
        }

        // Create agendamento
        $ag = Agendamento::create([
            'paciente_id' => $paciente->id,
            'scheduled_at' => $scheduled->toDateTimeString(),
            'duration_minutes' => 60,
            'status' => 'scheduled',
            'notes' => 'Solicitação via formulário público',
        ]);

        return view('solicitar_success', ['agendamento' => $ag, 'paciente' => $paciente]);
    }
}
