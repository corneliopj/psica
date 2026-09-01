<?php

namespace Database\Seeders;

use App\Models\Agendamento;
use App\Models\Paciente;
use App\Models\Profissional;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevelopmentSeed extends Seeder
{
    public function run(): void
    {
        $this->command->info('Gerando dados de desenvolvimento com 5 registros principais...');

        $usuarios = [
            [
                'nome' => 'Admin Psica',
                'email' => 'admin@psica.dev',
                'password' => Hash::make('password'),
                'perfil' => 'admin',
                'status' => 'ativo',
            ],
            [
                'nome' => 'Dra. Helena Costa',
                'email' => 'helena@psica.dev',
                'password' => Hash::make('password'),
                'perfil' => 'profissional',
                'status' => 'ativo',
            ],
            [
                'nome' => 'Mateus Silva',
                'email' => 'mateus@psica.dev',
                'password' => Hash::make('password'),
                'perfil' => 'paciente',
                'status' => 'ativo',
            ],
            [
                'nome' => 'Larissa Souza',
                'email' => 'larissa@psica.dev',
                'password' => Hash::make('password'),
                'perfil' => 'paciente',
                'status' => 'ativo',
            ],
            [
                'nome' => 'Gabriel Prado',
                'email' => 'gabriel@psica.dev',
                'password' => Hash::make('password'),
                'perfil' => 'paciente',
                'status' => 'ativo',
            ],
        ];

        $criados = [];
        foreach ($usuarios as $dados) {
            $criados[] = Usuario::create($dados);
        }

        $profissional = Profissional::create([
            'usuario_id' => $criados[1]->id,
            'nome' => 'Dra. Helena Costa',
            'especialidade' => 'Psicanálise Adultos',
            'telefone' => '(11) 99999-0001',
            'status' => 'ativo',
            'observacoes' => 'Atendimento online e presencial.',
        ]);

        $pacientes = [];
        foreach (array_slice($criados, 2) as $indice => $usuario) {
            $pacientes[] = Paciente::create([
                'usuario_id' => $usuario->id,
                'nome' => $usuario->nome,
                'telefone' => encrypt(sprintf('(11) 9%06d', ($indice + 1) * 7)),
                'cpf' => encrypt(sprintf('%011d', $indice + 1)),
                'data_nascimento' => now()->subYears(28 + $indice)->toDateString(),
                'contato_emergencia' => 'Contato ' . ($indice + 1),
                'status' => 'ativo',
            ]);
        }

        $base = now()->setTime(9, 0, 0);
        foreach ($pacientes as $indice => $paciente) {
            $inicio = $base->copy()->addDays($indice)->addHours($indice % 3);
            Agendamento::create([
                'profissional_id' => $profissional->id,
                'paciente_id' => $paciente->id,
                'data_hora_inicio' => $inicio,
                'data_hora_fim' => $inicio->copy()->addMinutes(50),
                'status' => $indice === 0 ? 'confirmado' : ($indice === 1 ? 'solicitado' : 'realizado'),
                'observacoes_cancelamento' => null,
            ]);
        }
    }
}
