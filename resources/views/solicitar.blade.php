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
            <!-- keep the ISO input hidden for submission, show a localized text input for users -->
            <input id="public_scheduled_at" type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="hidden">
            <input id="public_scheduled_text" type="text" placeholder="DD/MM/YYYY HH:mm" class="mt-1 block w-full border rounded p-2" value="" required>
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
                const isoInput = document.getElementById('public_scheduled_at');
                const textInput = document.getElementById('public_scheduled_text');
                const display = document.getElementById('public_scheduled_display');

                function parseBR(str){
                    // expected DD/MM/YYYY HH:mm
                    const m = str.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})\s+(\d{1,2}):(\d{2})$/);
                    if(!m) return null;
                    const day = parseInt(m[1],10), month = parseInt(m[2],10)-1, year = parseInt(m[3],10);
                    const hour = parseInt(m[4],10), minute = parseInt(m[5],10);
                    const d = new Date(year, month, day, hour, minute);
                    if(d.getFullYear()!==year || d.getMonth()!==month || d.getDate()!==day) return null;
                    return d;
                }

                function toISOForInput(d){
                    const tzOffset = d.getTimezoneOffset()*60000;
                    const local = new Date(d.getTime() - tzOffset);
                    return local.toISOString().slice(0,16);
                }

                function updateFromIso(){
                    if(!isoInput.value){ display.innerText = ''; textInput.value = ''; return }
                    const d = new Date(isoInput.value);
                    if(isNaN(d)) { display.innerText = isoInput.value; textInput.value = ''; return }
                    display.innerText = formatBR(d);
                    textInput.value = formatBR(d);
                }

                function updateIsoFromText(){
                    const txt = textInput.value.trim();
                    const d = parseBR(txt);
                    if(!d){ display.innerText = 'Formato inválido (use DD/MM/YYYY HH:mm)'; return }
                    isoInput.value = toISOForInput(d);
                    display.innerText = formatBR(d);
                }

                textInput.addEventListener('change', updateIsoFromText);
                textInput.addEventListener('blur', updateIsoFromText);
                document.addEventListener('DOMContentLoaded', updateFromIso);
                // populate initial from server old value if any
                updateFromIso();
            })();
        </script>
        <div>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Solicitar sessão</button>
        </div>
    </form>
    </div>
</x-guest-layout>
