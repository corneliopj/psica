<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Paciente;
use App\Models\Prontuario;
use App\Models\Agendamento;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create sample pacientes
        $pacientes = [];
        for ($i = 1; $i <= 5; $i++) {
            $p = Paciente::create([
                'name' => "Paciente {$i}",
                'email' => "paciente{$i}@example.com",
                'phone' => '1199999'.str_pad($i,4,'0',STR_PAD_LEFT),
                'birth_date' => now()->subYears(20 + $i)->toDateString(),
                'notes' => 'Registro de exemplo',
            ]);
            $pacientes[] = $p;
        }

        // Create some prontuarios and agendamentos for pacientes
        foreach ($pacientes as $idx => $paciente) {
            // 1 or 2 prontuarios
            $countPr = ($idx % 2) + 1;
            for ($j = 0; $j < $countPr; $j++) {
                Prontuario::create([
                    'paciente_id' => $paciente->id,
                    'title' => "Prontuário {$j} - {$paciente->name}",
                    'content' => 'Conteúdo do prontuário de exemplo',
                    'created_by' => 1,
                ]);
            }

            // 1-3 agendamentos
            $countA = 1 + ($idx % 3);
            for ($k = 0; $k < $countA; $k++) {
                Agendamento::create([
                    'paciente_id' => $paciente->id,
                    'scheduled_at' => now()->addDays($k + $idx)->toDateTimeString(),
                    'duration_minutes' => 50,
                    'status' => 'scheduled',
                    'notes' => 'Agendamento de teste',
                ]);
            }
        }
    }
}
