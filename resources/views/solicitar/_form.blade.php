@php
    $usuarioLogado = auth()->user();
    $ehPacienteLogado = $usuarioLogado?->perfil === 'paciente';
    $pacienteLogado = null;

    if ($ehPacienteLogado) {
        $pacienteLogado = \App\Models\Paciente::query()->where('usuario_id', $usuarioLogado->id)->first();
    }

    $nomePadrao = old('name', $usuarioLogado?->nome ?? $usuarioLogado?->name ?? '');
    $telefonePadrao = old('phone', $pacienteLogado?->phone ?? '');
    $profissionaisDisponiveis = \App\Models\Profissional::query()
        ->where('status', 'ativo')
        ->orderBy('nome')
        ->get(['id', 'nome', 'especialidade']);
@endphp

@if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 p-4 mb-4">
        <ul class="list-disc ml-5">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('solicitar.store') }}" class="space-y-4">
    @csrf
    @if($ehPacienteLogado)
        <input type="hidden" name="name" value="{{ $nomePadrao }}">
        <input type="hidden" name="phone" value="{{ $telefonePadrao }}">
        <p class="rounded border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-800">
            Solicitando como <strong>{{ $nomePadrao }}</strong>.
        </p>
    @else
        <div>
            <label class="block text-sm font-medium text-gray-700">Nome</label>
            <input name="name" value="{{ $nomePadrao }}" required class="mt-1 block w-full border rounded p-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Telefone</label>
            <input name="phone" value="{{ $telefonePadrao }}" required class="mt-1 block w-full border rounded p-2" placeholder="(11) 9XXXX-XXXX">
        </div>
    @endif

    <div>
        <label class="block text-sm font-medium text-gray-700">1. Escolha o doutor</label>
        <select id="public_profissional_id" name="profissional_id" required class="mt-1 block w-full border rounded p-2">
            <option value="">Selecione...</option>
            @foreach($profissionaisDisponiveis as $profissional)
                <option value="{{ $profissional->id }}" @selected(old('profissional_id') == $profissional->id)>
                    {{ $profissional->nome }}{{ $profissional->especialidade ? ' - '.$profissional->especialidade : '' }}
                </option>
            @endforeach
        </select>
        @error('profissional_id')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">2. Escolha um horário</label>
        <input id="public_scheduled_at" type="hidden" name="scheduled_at" value="{{ old('scheduled_at') }}" required>
        <p id="public-calendar-status" class="mt-3 text-sm text-gray-600">Escolha o doutor para carregar os horários.</p>
        <div id="public-calendar-wrapper" class="hidden">
            <div id="public-calendar" class="mt-2 overflow-hidden rounded-lg border border-gray-200 bg-white"></div>
            <div id="public-time-options" class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3"></div>
        </div>
        @error('scheduled_at')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Solicitar sessão</button>
    </div>
</form>