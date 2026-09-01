@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl mb-4">Novo Paciente</h1>
    <form action="{{ route('pacientes.store') }}" method="POST">@csrf
        @include('pacientes._form')
    </form>
</div>
@endsection
