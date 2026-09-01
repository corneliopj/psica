@extends('layouts.app')

@section('content')
<div class="container p-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl">Agendamentos</h1>
        <a href="{{ route('agendamentos.create') }}" class="btn">Novo</a>
    </div>
    <ul>
        @foreach($agendamentos as $a)
            <li class="border-b py-2">
                <a href="{{ route('agendamentos.show', $a) }}">{{ $a->paciente->name ?? '—' }} — {{ $a->scheduled_at }}</a>
            </li>
        @endforeach
    </ul>
    <div class="mt-4">{{ $agendamentos->links() }}</div>
</div>
@endsection
