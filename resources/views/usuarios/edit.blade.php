<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar usuário</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('usuarios.update', $usuario) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm mb-1">Nome</label>
                        <input name="nome" value="{{ old('nome', $usuario->nome) }}" class="w-full border rounded p-2" required>
                    </div>

                    <div>
                        <label class="block text-sm mb-1">E-mail</label>
                        <input name="email" type="email" value="{{ old('email', $usuario->email) }}" class="w-full border rounded p-2" required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm mb-1">Perfil</label>
                            <select name="perfil" class="w-full border rounded p-2">
                                @foreach(['admin' => 'Administrador', 'profissional' => 'Doutor', 'paciente' => 'Paciente'] as $valor => $rotulo)
                                    <option value="{{ $valor }}" @selected(old('perfil', $usuario->perfil) === $valor)>{{ $rotulo }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm mb-1">Status</label>
                            <select name="status" class="w-full border rounded p-2">
                                @foreach(['ativo' => 'Ativo', 'inativo' => 'Inativo', 'suspenso' => 'Suspenso'] as $valor => $rotulo)
                                    <option value="{{ $valor }}" @selected(old('status', $usuario->status) === $valor)>{{ $rotulo }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Nova senha</label>
                        <input name="password" type="password" class="w-full border rounded p-2">
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('usuarios.index') }}" class="px-3 py-2 border rounded">Cancelar</a>
                        <button type="submit" class="px-3 py-2 bg-indigo-600 text-white rounded">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>