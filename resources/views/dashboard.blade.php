<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                @php
                    $pacientes = \App\Models\Paciente::count();
                    $prontuarios = \App\Models\Prontuario::count();
                    $agendamentos = \App\Models\Agendamento::count();
                @endphp

                <a href="{{ route('pacientes.index') }}" class="block">
                    <div class="bg-white shadow sm:rounded-lg p-6 hover:shadow-lg transition">
                        <div class="text-sm text-gray-500">Pacientes</div>
                        <div class="mt-2 text-2xl font-bold">{{ $pacientes }}</div>
                        <div class="mt-4 text-sm text-indigo-600">Ver pacientes &rarr;</div>
                    </div>
                </a>

                <a href="{{ route('prontuarios.index') }}" class="block">
                    <div class="bg-white shadow sm:rounded-lg p-6 hover:shadow-lg transition">
                        <div class="text-sm text-gray-500">Prontuários</div>
                        <div class="mt-2 text-2xl font-bold">{{ $prontuarios }}</div>
                        <div class="mt-4 text-sm text-indigo-600">Ver prontuários &rarr;</div>
                    </div>
                </a>

                <a href="{{ route('agendamentos.index') }}" class="block">
                    <div class="bg-white shadow sm:rounded-lg p-6 hover:shadow-lg transition">
                        <div class="text-sm text-gray-500">Agendamentos</div>
                        <div class="mt-2 text-2xl font-bold">{{ $agendamentos }}</div>
                        <div class="mt-4 text-sm text-indigo-600">Ver agendamentos &rarr;</div>
                    </div>
                </a>
            </div>
            <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="col-span-2 bg-white shadow sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Agenda (calendário)</h3>
                    <div id="calendar"></div>
                </div>

                <div class="bg-white shadow sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Próximos agendamentos</h3>
                <h3 class="text-lg font-semibold mb-4">Próximos agendamentos</h3>
                @php
                    $upcoming = \App\Models\Agendamento::with('paciente')->where('scheduled_at', '>=', now())->orderBy('scheduled_at')->limit(10)->get();
                @endphp
                @if($upcoming->isEmpty())
                    <div class="text-gray-500">Nenhum agendamento futuro.</div>
                @else
                    <ul class="space-y-3">
                        @foreach($upcoming as $a)
                            <li class="flex items-center justify-between border p-3 rounded">
                                <div>
                                    <div class="font-medium">{{ $a->paciente?->name ?? 'Paciente não identificado' }}</div>
                                    <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($a->scheduled_at)->format('d/m/Y H:i') }}</div>
                                </div>
                                <div>
                                    <a href="{{ route('pacientes.show', $a->paciente_id) }}" class="text-indigo-600">Ver paciente</a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
