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

        </div>
    </div>
</x-app-layout>
