<?php

namespace App\Http\Controllers;

use App\Models\Profissional;
use App\Models\Slot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SlotController extends Controller
{
    protected function garantirPerfilPermitido(Request $request, array $perfisPermitidos): void
    {
        abort_unless(in_array($request->user()?->perfil, $perfisPermitidos, true), 403);
    }

    protected function usuarioRelacionamentoId(): string
    {
        return \Illuminate\Support\Facades\Schema::hasColumn('slots', 'usuario_id') ? 'usuario_id' : 'user_id';
    }

    public function index()
    {
        $slots = Slot::orderBy('start')->get();
        return view('slots.index', compact('slots'));
    }

    public function store(Request $request)
    {
        $this->garantirPerfilPermitido($request, ['admin', 'profissional']);
        $data = $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'repeat_weekly' => 'nullable|boolean',
            'repeat_until' => 'nullable|date',
        ]);

        $start = new \DateTime($data['start']);
        $end = new \DateTime($data['end']);
        $duration = $end->getTimestamp() - $start->getTimestamp();

        $created = [];
        if(!empty($data['repeat_weekly']) && !empty($data['repeat_until'])){
            $until = new \DateTime($data['repeat_until']);
            $current = clone $start;
            while($current <= $until){
                $slotEnd = (clone $current)->modify('+' . ($duration / 60) . ' minutes');
                $slot = Slot::create([
                    'start' => $current->format('Y-m-d H:i:s'),
                    'end' => $slotEnd->format('Y-m-d H:i:s'),
                    'status' => 'free',
                    $this->usuarioRelacionamentoId() => Auth::id(),
                    'recurrence_rule' => 'WEEKLY'
                ]);
                $created[] = $slot;
                $current = (clone $current)->modify('+7 days');
            }
        } else {
            $slot = Slot::create([
                'start' => $start->format('Y-m-d H:i:s'),
                'end' => $end->format('Y-m-d H:i:s'),
                'status' => 'free',
                $this->usuarioRelacionamentoId() => Auth::id(),
            ]);
            $created[] = $slot;
        }

        if($request->wantsJson()){
            return response()->json(['created' => $created]);
        }

        return back()->with('success', 'Slots criados');
    }

    public function update(Request $request, Slot $slot)
    {
        $this->garantirPerfilPermitido($request, ['admin', 'profissional']);
        // authorization handled by middleware for now
        $data = $request->validate([
            'status' => 'required|in:free,occupied'
        ]);
        $slot->update(['status' => $data['status']]);
        if($request->wantsJson()) return response()->json(['slot' => $slot]);
        return back();
    }

    public function destroy(Slot $slot)
    {
        $this->garantirPerfilPermitido(request(), ['admin', 'profissional']);
        // authorization handled by middleware for now
        $slot->delete();
        return back();
    }

    // Public API to list slots for calendar
    public function apiIndex(Request $request)
    {
        $slotUserColumn = $this->usuarioRelacionamentoId();
        $profissionalId = $request->query('profissional_id');

        $query = Slot::query()->orderBy('start');

        if (!empty($profissionalId)) {
            $profissional = Profissional::query()->find($profissionalId);

            if (!$profissional instanceof Profissional) {
                return response()->json([]);
            }

            $query->where($slotUserColumn, $profissional->usuario_id);
        }

        $slots = $query->get()->map(function($s) use ($slotUserColumn){
            return [
                'id' => $s->id,
                'start' => $s->start->format('Y-m-d\TH:i:s'),
                'end' => $s->end->format('Y-m-d\TH:i:s'),
                'status' => $s->status,
                'usuario_id' => $s->{$slotUserColumn},
            ];
        });
        return response()->json($slots);
    }
}
