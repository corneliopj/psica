<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Usuários</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                @if(session('success'))
                    <div class="mb-4 rounded border border-green-200 bg-green-50 p-3 text-green-700">{{ session('success') }}</div>
                @endif

                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b text-left text-gray-500">
                            <th class="py-2 pr-4">Nome</th>
                            <th class="py-2 pr-4">E-mail</th>
                            <th class="py-2 pr-4">Perfil</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usuarios as $usuario)
                            <tr class="border-b">
                                <td class="py-3 pr-4">{{ $usuario->nome }}</td>
                                <td class="py-3 pr-4">{{ $usuario->email }}</td>
                                <td class="py-3 pr-4">{{ ucfirst($usuario->perfil) }}</td>
                                <td class="py-3 pr-4">{{ ucfirst($usuario->status) }}</td>
                                <td class="py-3">
                                    <a href="{{ route('usuarios.edit', $usuario) }}" class="text-indigo-600">Editar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4">{{ $usuarios->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>