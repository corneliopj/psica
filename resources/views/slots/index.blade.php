<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Horários (Slots)') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="mb-4">
                    <a href="{{ route('dashboard') }}" class="text-indigo-600">&larr; Voltar</a>
                </div>
                <div class="mb-4">
                    <a href="#" id="openCreateSlot" class="px-3 py-2 bg-green-600 text-white rounded">Criar novo slot</a>
                </div>

                @if(session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-700 p-3 mb-4">{{ session('success') }}</div>
                @endif

                <table class="w-full table-auto border-collapse">
                    <thead>
                        <tr class="text-left border-b">
                            <th class="p-2">Início</th>
                            <th class="p-2">Fim</th>
                            <th class="p-2">Status</th>
                            <th class="p-2">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($slots as $s)
                            <tr class="border-b">
                                <td class="p-2">{{ \Carbon\Carbon::parse($s->start)->format('d/m/Y H:i') }}</td>
                                <td class="p-2">{{ \Carbon\Carbon::parse($s->end)->format('d/m/Y H:i') }}</td>
                                <td class="p-2">{{ ucfirst($s->status) }}</td>
                                <td class="p-2">
                                    <form method="POST" action="{{ route('slots.update', $s) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $s->status === 'free' ? 'occupied' : 'free' }}">
                                        <button class="px-2 py-1 bg-blue-600 text-white rounded">{{ $s->status === 'free' ? 'Ocupar' : 'Liberar' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('slots.destroy', $s) }}" class="inline" onsubmit="return confirm('Remover este slot?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="px-2 py-1 bg-red-600 text-white rounded ml-2">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('openCreateSlot')?.addEventListener('click', function(e){
            e.preventDefault();
            // open slot creation modal from dashboard (reuse existing slot modal if present)
            const m = document.getElementById('slotModal'); if(m) m.classList.remove('hidden');
        });
    </script>
</x-app-layout>
