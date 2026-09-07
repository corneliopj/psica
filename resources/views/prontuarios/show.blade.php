@extends('layouts.app')

@section('content')
<div class="container p-6">
    <h1 class="text-2xl">Prontuário #{{ $prontuario->id }}</h1>
    <div class="text-sm text-gray-600">Paciente: {{ $prontuario->paciente->name ?? $prontuario->paciente->nome ?? '—' }}</div>
    <div class="text-sm text-gray-600">Status: {{ $prontuario->selado ? 'Selado' : 'Aberto' }}</div>
    @if($prontuario->selado)
        <div class="text-xs text-gray-500 mt-2">Hash integridade: {{ $prontuario->hash_integridade }}</div>
    @endif
    <div class="mt-4">
        <h2 class="font-semibold">Anotações</h2>
        <div class="whitespace-pre-wrap">{{ $prontuario->anotacoes }}</div>
    </div>
    <div class="mt-4">
        <h2 class="font-semibold">Histórico clínico</h2>
        <div class="whitespace-pre-wrap">{{ $prontuario->historico_clinico }}</div>
    </div>
    <div class="mt-4">
        <a href="{{ route('prontuarios.edit', $prontuario) }}" class="btn">Editar</a>
    </div>
</div>
@endsection
