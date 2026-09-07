<?php

namespace App\Http\Controllers;

use App\Contracts\ProntuarioServiceContract;
use App\Http\Requests\ProntuarioRequest;
use App\Models\Agendamento;
use App\Models\Prontuario;
use App\Models\Paciente;
use App\Models\Profissional;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ProntuarioController extends Controller
{
    public function __construct(
        protected ProntuarioServiceContract $prontuarioService,
    ) {}

    protected function garantirPerfilPermitido(): void
    {
        abort_unless(in_array(request()->user()?->perfil, ['admin', 'profissional'], true), 403);
    }

    public function index(): View
    {
        $this->garantirPerfilPermitido();
        $prontuarios = Prontuario::with('paciente')->latest()->paginate(20);
        return view('prontuarios.index', compact('prontuarios'));
    }

    public function create(): View
    {
        $this->garantirPerfilPermitido();
        $pacientes = Paciente::orderBy(Schema::hasColumn('pacientes', 'nome') ? 'nome' : 'name')->get();
        $profissionais = Profissional::query()->orderBy('nome')->get();
        $agendamentos = Agendamento::query()->orderBy(Agendamento::startColumn(), 'desc')->limit(200)->get();
        return view('prontuarios.create', compact('pacientes', 'profissionais', 'agendamentos'));
    }

    public function store(ProntuarioRequest $request): RedirectResponse
    {
        $this->garantirPerfilPermitido();

        $data = $request->validated();
        $this->prontuarioService->criarProntuario(
            (int) $data['agendamento_id'],
            (int) $data['paciente_id'],
            (int) $data['profissional_id'],
            $data['anotacoes'] ?? null,
            $data['historico_clinico'] ?? null,
        );

        return redirect()->route('prontuarios.index')->with('success', 'Prontuário criado.');
    }

    public function show(Prontuario $prontuario): View
    {
        $perfil = request()->user()?->perfil;
        abort_unless(in_array($perfil, ['admin', 'profissional', 'paciente'], true), 403);
        $prontuario->load('paciente');
        return view('prontuarios.show', compact('prontuario'));
    }

    public function edit(Prontuario $prontuario): View
    {
        $this->garantirPerfilPermitido();
        $pacientes = Paciente::orderBy(Schema::hasColumn('pacientes', 'nome') ? 'nome' : 'name')->get();
        $profissionais = Profissional::query()->orderBy('nome')->get();
        $agendamentos = Agendamento::query()->orderBy(Agendamento::startColumn(), 'desc')->limit(200)->get();
        return view('prontuarios.edit', compact('prontuario', 'pacientes', 'profissionais', 'agendamentos'));
    }

    public function update(ProntuarioRequest $request, Prontuario $prontuario): RedirectResponse
    {
        $this->garantirPerfilPermitido();

        $this->prontuarioService->atualizarProntuario(
            $prontuario->id,
            (int) request()->user()->id,
            $request->validated(),
        );

        return redirect()->route('prontuarios.show', $prontuario)->with('success', 'Prontuário atualizado.');
    }

    public function destroy(Prontuario $prontuario): RedirectResponse
    {
        $this->garantirPerfilPermitido();
        $prontuario->delete();
        return redirect()->route('prontuarios.index')->with('success', 'Prontuário removido.');
    }

    public function registrarDiario(Request $request, Prontuario $prontuario): JsonResponse
    {
        $this->garantirPerfilPermitido();

        $data = $request->validate([
            'anotacoes' => ['required', 'string'],
            'historico_clinico' => ['nullable', 'string'],
        ]);

        $prontuario = $this->prontuarioService->registrarDiarioSessao(
            $prontuario->id,
            (int) $request->user()->id,
            $data['anotacoes'],
            $data['historico_clinico'] ?? null,
        );

        return response()->json([
            'prontuario' => [
                'id' => $prontuario->id,
                'selado' => (bool) $prontuario->selado,
                'data_registro' => $prontuario->data_registro,
            ],
        ]);
    }

    public function selar(Request $request, Prontuario $prontuario): JsonResponse
    {
        $this->garantirPerfilPermitido();

        $prontuario = $this->prontuarioService->selarRegistroSessao(
            $prontuario->id,
            (int) $request->user()->id,
        );

        return response()->json([
            'prontuario' => [
                'id' => $prontuario->id,
                'selado' => (bool) $prontuario->selado,
                'data_selamento' => $prontuario->data_selamento,
                'hash_integridade' => $prontuario->hash_integridade,
            ],
        ]);
    }
}
