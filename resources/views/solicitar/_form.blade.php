@php
    $usuarioLogado = auth()->user();
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
    <div>
        <label class="block text-sm font-medium text-gray-700">Nome</label>
        <input name="name" value="{{ old('name', $usuarioLogado?->nome) }}" required class="mt-1 block w-full border rounded p-2">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Telefone</label>
        <input name="phone" value="{{ old('phone') }}" required class="mt-1 block w-full border rounded p-2" placeholder="(11) 9XXXX-XXXX">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Escolha um horário</label>
        <input id="public_scheduled_at" type="hidden" name="scheduled_at" value="{{ old('scheduled_at') }}" required>
        <div id="public-calendar" class="mt-2 overflow-hidden rounded-lg border border-gray-200 bg-white"></div>
        <p id="public-calendar-status" class="mt-3 text-sm text-gray-600">Carregando horários disponíveis...</p>
        <div id="public-time-options" class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3"></div>
        @error('scheduled_at')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Solicitar sessão</button>
    </div>
</form>