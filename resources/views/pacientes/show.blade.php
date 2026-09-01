@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl">{{ $paciente->name }}</h1>
    <p><strong>Email:</strong> {{ $paciente->email }}</p>
    <p><strong>Telefone:</strong> {{ $paciente->phone }}</p>
    <p><strong>Data de Nascimento:</strong> {{ $paciente->birth_date }}</p>
    <p class="mt-4">{{ $paciente->notes }}</p>
    <div class="mt-4">
        <a href="{{ route('pacientes.edit', $paciente) }}" class="btn">Editar</a>
    </div>
</div>
@endsection
