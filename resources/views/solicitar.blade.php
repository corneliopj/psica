<x-guest-layout>
    <div class="max-w-2xl mx-auto py-12">
    <h1 class="text-2xl font-bold mb-4">Solicitar sessão</h1>

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
            <input name="name" value="{{ old('name') }}" required class="mt-1 block w-full border rounded p-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Telefone</label>
            <input name="phone" value="{{ old('phone') }}" required class="mt-1 block w-full border rounded p-2" placeholder="(11) 9XXXX-XXXX">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Horário (data e hora)</label>
            <input id="public_scheduled_at" type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" required class="mt-1 block w-full border rounded p-2">
            <div id="public_scheduled_display" class="mt-2 text-sm text-gray-700"></div>
        </div>
        <script>
            (function(){
                function pad(n){ return n<10 ? '0'+n : ''+n }
                function formatBR(d){
                    const day = pad(d.getDate());
                    const month = pad(d.getMonth()+1);
                    const year = d.getFullYear();
                    const hours = pad(d.getHours());
                    const mins = pad(d.getMinutes());
                    return `${day}/${month}/${year} ${hours}:${mins}`;
                }
                const input = document.getElementById('public_scheduled_at');
                const display = document.getElementById('public_scheduled_display');
                function update(){
                    if(!input.value){ display.innerText = '' ; return }
                    const d = new Date(input.value);
                    if(isNaN(d)) { display.innerText = input.value; return }
                    display.innerText = formatBR(d);
                }
                input.addEventListener('change', update);
                document.addEventListener('DOMContentLoaded', update);
                // initial update if server provided an old value
                update();
            })();
        </script>
        <div>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Solicitar sessão</button>
        </div>
    </form>
    </div>
</x-guest-layout>
