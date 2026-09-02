<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paciente;
use App\Models\Agendamento;
use App\Models\Slot;

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

        $availableSlot = Slot::where('status', 'free')
            ->where('start', '<=', $scheduled)
            ->where('end', '>=', $scheduled->copy()->addHour())
            ->exists();

        if (!$availableSlot) {
            return back()->withInput()->withErrors(['scheduled_at' => 'Selecione um horário disponível no calendário.']);
        }

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

    // Return JSON events for calendar
    public function events(Request $request)
    {
        $from = $request->query('start');
        $to = $request->query('end');

        $query = Agendamento::with('paciente')->whereBetween('scheduled_at', [$from ?? now()->subMonth(), $to ?? now()->addMonth()])->get();

        $events = $query->map(function($a){
            return [
                'id' => $a->id,
                'title' => $a->paciente?->name ?? 'Paciente',
                'start' => $a->scheduled_at,
                'end' => \Carbon\Carbon::parse($a->scheduled_at)->addMinutes($a->duration_minutes)->toDateTimeString(),
                'extendedProps' => [
                    'paciente_id' => $a->paciente_id,
                ],
            ];
        });

        return response()->json($events);
    }

    // API store for calendar booking (AJAX)
    public function apiStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
            'scheduled_at' => 'required|date',
        ]);

        $phone = preg_replace('/[^0-9+]/', '', $data['phone']);
        $paciente = Paciente::firstOrCreate(['phone' => $phone], ['name' => $data['name'], 'email' => null]);

        $scheduled = \Carbon\Carbon::parse($data['scheduled_at']);

        // Check overlap
        $overlap = Agendamento::where(function($q) use ($scheduled){
            $q->where('scheduled_at', '<', $scheduled->copy()->addHour())
              ->whereRaw("datetime(scheduled_at, '+' || duration_minutes || ' minutes') > ?", [$scheduled->toDateTimeString()]);
        })->exists();

        if($overlap){
            return response()->json(['error' => 'Horário ocupado'], 422);
        }

        $ag = Agendamento::create([
            'paciente_id' => $paciente->id,
            'scheduled_at' => $scheduled->toDateTimeString(),
            'duration_minutes' => 60,
            'status' => 'scheduled',
            'notes' => 'Solicitação via calendar',
        ]);

        return response()->json(['success' => true, 'event' => $ag]);
    }
}
