<div>
    <div class="mb-4">
        <label class="block">Agendamento</label>
        <select name="agendamento_id" class="w-full border p-2" required>
            @foreach($agendamentos as $ag)
                <option value="{{ $ag->id }}" {{ (old('agendamento_id', $prontuario->agendamento_id ?? '') == $ag->id) ? 'selected' : '' }}>
                    #{{ $ag->id }} - {{ \Carbon\Carbon::parse($ag->scheduled_at)->format('d/m/Y H:i') }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="mb-4">
        <label class="block">Paciente</label>
        <select name="paciente_id" class="w-full border p-2" required>
            @foreach($pacientes as $pt)
                <option value="{{ $pt->id }}" {{ (old('paciente_id', $prontuario->paciente_id ?? '') == $pt->id) ? 'selected' : '' }}>{{ $pt->name ?? $pt->nome }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-4">
        <label class="block">Psicanalista</label>
        <select name="profissional_id" class="w-full border p-2" required>
            @foreach($profissionais as $profissional)
                <option value="{{ $profissional->id }}" {{ (old('profissional_id', $prontuario->profissional_id ?? '') == $profissional->id) ? 'selected' : '' }}>{{ $profissional->nome }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-4">
        <label class="block">Anotações</label>
        <textarea name="anotacoes" class="w-full border p-2" rows="5">{{ old('anotacoes', $prontuario->anotacoes ?? '') }}</textarea>
    </div>
    <div class="mb-4">
        <label class="block">Histórico clínico</label>
        <textarea name="historico_clinico" class="w-full border p-2" rows="5">{{ old('historico_clinico', $prontuario->historico_clinico ?? '') }}</textarea>
    </div>
    <div>
        <button class="btn">Salvar</button>
    </div>
</div>
