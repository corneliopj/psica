@extends('layouts.app')

@section('content')
<div class="container p-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl">Prontuários</h1>
        <a href="{{ route('prontuarios.create') }}" class="btn">Novo</a>
    </div>
    <ul>
        @foreach($prontuarios as $p)
            <li class="border-b py-2">
                <a href="{{ route('prontuarios.show', $p) }}">Prontuário #{{ $p->id }}</a>
                <div class="text-sm text-gray-600">Paciente: {{ $p->paciente->name ?? $p->paciente->nome ?? '—' }}</div>
                <div class="text-sm text-gray-600">Status: {{ $p->selado ? 'Selado' : 'Aberto' }}</div>
            </li>
        @endforeach
    </ul>
    <div class="mt-4">{{ $prontuarios->links() }}</div>
</div>
@endsection
