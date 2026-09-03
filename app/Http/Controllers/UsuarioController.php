<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->perfil === 'admin', 403);

        $usuarios = Usuario::query()->orderBy('nome')->paginate(20);

        return view('usuarios.index', compact('usuarios'));
    }

    public function edit(Request $request, Usuario $usuario): View
    {
        abort_unless($request->user()?->perfil === 'admin', 403);

        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, Usuario $usuario): RedirectResponse
    {
        abort_unless($request->user()?->perfil === 'admin', 403);

        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:usuarios,email,' . $usuario->id],
            'perfil' => ['required', 'in:admin,profissional,paciente'],
            'status' => ['required', 'in:ativo,inativo,suspenso'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        if (! empty($dados['password'])) {
            $dados['password'] = Hash::make($dados['password']);
        } else {
            unset($dados['password']);
        }

        $usuario->update($dados);

        return redirect()->route('usuarios.index')->with('success', 'Usuário atualizado.');
    }
}