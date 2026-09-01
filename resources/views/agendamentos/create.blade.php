@extends('layouts.app')

@section('content')
<div class="container p-6">
    <h1 class="text-2xl mb-4">Novo Agendamento</h1>
    <form action="{{ route('agendamentos.store') }}" method="POST">@csrf
        @include('agendamentos._form')
    </form>
</div>
@endsection
