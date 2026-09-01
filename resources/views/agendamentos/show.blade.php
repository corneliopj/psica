@extends('layouts.app')

@section('content')
<div class="container p-6">
    <h1 class="text-2xl">Agendamento #{{ $agendamento->id }}</h1>
    <div class="text-sm text-gray-600">Paciente: {{ $agendamento->paciente->name ?? '—' }}</div>
    <div class="mt-4">Data: {{ $agendamento->scheduled_at }}</div>
    <div class="mt-4">Duração: {{ $agendamento->duration_minutes }} minutos</div>
    <div class="mt-4">{{ $agendamento->notes }}</div>
    <div class="mt-4">
        <a href="{{ route('agendamentos.edit', $agendamento) }}" class="btn">Editar</a>
    </div>
</div>
@endsection
