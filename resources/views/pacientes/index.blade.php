@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Pacientes</h1>
        <a href="{{ route('pacientes.create') }}" class="btn">Novo Paciente</a>
    </div>

    @if(session('success'))
        <div class="mb-4 text-green-600">{{ session('success') }}</div>
    @endif

    <table class="w-full table-auto">
        <thead>
            <tr>
                <th class="text-left">Nome</th>
                <th class="text-left">Email</th>
                <th class="text-left">Telefone</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @foreach($pacientes as $p)
            <tr class="border-t">
                <td>{{ $p->name }}</td>
                <td>{{ $p->email }}</td>
                <td>{{ $p->phone }}</td>
                <td class="text-right">
                    <a href="{{ route('pacientes.show', $p) }}" class="mr-2">Ver</a>
                    <a href="{{ route('pacientes.edit', $p) }}" class="mr-2">Editar</a>
                    <form action="{{ route('pacientes.destroy', $p) }}" method="POST" style="display:inline">@csrf @method('DELETE')<button class="text-red-600">Remover</button></form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="mt-4">{{ $pacientes->links() }}</div>
</div>
@endsection
