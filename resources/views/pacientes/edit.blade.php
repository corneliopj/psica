@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl mb-4">Editar Paciente</h1>
    <form action="{{ route('pacientes.update', $paciente) }}" method="POST">@csrf @method('PUT')
        @include('pacientes._form')
    </form>
</div>
@endsection
