<div>
    <div class="mb-4">
        <label class="block">Paciente</label>
        <select name="paciente_id" class="w-full border p-2">
            @foreach($pacientes as $pt)
                <option value="{{ $pt->id }}" {{ (old('paciente_id', $agendamento->paciente_id ?? '') == $pt->id) ? 'selected' : '' }}>{{ $pt->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-4">
        <label class="block">Data e hora</label>
        <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', isset($agendamento) ? date('Y-m-d\TH:i', strtotime($agendamento->scheduled_at)) : '') }}" class="w-full border p-2">
    </div>
    <div class="mb-4">
        <label class="block">Duração (min)</label>
        <input name="duration_minutes" value="{{ old('duration_minutes', $agendamento->duration_minutes ?? 60) }}" class="w-full border p-2">
    </div>
    <div class="mb-4">
        <label class="block">Notas</label>
        <textarea name="notes" class="w-full border p-2">{{ old('notes', $agendamento->notes ?? '') }}</textarea>
    </div>
    <div>
        <button class="btn">Salvar</button>
    </div>
</div>
