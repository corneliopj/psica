@extends('layouts.app')

@section('content')
<div class="container p-6">
    <h1 class="text-2xl">{{ $prontuario->title ?? 'Prontuário #' . $prontuario->id }}</h1>
    <div class="text-sm text-gray-600">Paciente: {{ $prontuario->patient->name ?? '—' }}</div>
    <div class="mt-4 whitespace-pre-wrap">{{ $prontuario->content }}</div>
    <div class="mt-4">
        <a href="{{ route('prontuarios.edit', $prontuario) }}" class="btn">Editar</a>
    </div>
</div>
@endsection
