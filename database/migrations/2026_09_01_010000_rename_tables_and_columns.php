<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename patients -> pacientes
        if (Schema::hasTable('patients') && !Schema::hasTable('pacientes')) {
            Schema::rename('patients', 'pacientes');
        }

        // Rename appointments -> agendamentos
        if (Schema::hasTable('appointments') && !Schema::hasTable('agendamentos')) {
            Schema::rename('appointments', 'agendamentos');
        }

        // Update prontuarios foreign key column patient_id -> paciente_id
        if (Schema::hasTable('prontuarios')) {
            // add new column, copy data, create FK, drop old column
            Schema::table('prontuarios', function (Blueprint $table) {
                if (! Schema::hasColumn('prontuarios', 'paciente_id') && Schema::hasColumn('prontuarios', 'patient_id')) {
                    $table->unsignedBigInteger('paciente_id')->nullable()->after('id');
                }
            });
            // copy values
            try {
                \DB::table('prontuarios')->whereNotNull('patient_id')->updateUsing(null, [ 'paciente_id' => \DB::raw('patient_id') ]);
            } catch (\Throwable $e) {
                // fallback copy
                \DB::statement('UPDATE prontuarios SET paciente_id = patient_id');
            }
            Schema::table('prontuarios', function (Blueprint $table) {
                if (Schema::hasColumn('prontuarios', 'paciente_id')) {
                    if (Schema::hasColumn('prontuarios', 'patient_id')) {
                        try { $table->dropForeign(['patient_id']); } catch (\Throwable $e) {}
                        try { $table->dropIndex(['patient_id']); } catch (\Throwable $e) {}
                        $table->dropColumn('patient_id');
                    }
                }
            });
        }

        // Update agendamentos foreign key column patient_id -> paciente_id if exists
        if (Schema::hasTable('agendamentos')) {
            Schema::table('agendamentos', function (Blueprint $table) {
                if (! Schema::hasColumn('agendamentos', 'paciente_id') && Schema::hasColumn('agendamentos', 'patient_id')) {
                    $table->unsignedBigInteger('paciente_id')->nullable()->after('id');
                }
            });
            try {
                \DB::statement('UPDATE agendamentos SET paciente_id = patient_id');
            } catch (\Throwable $e) {}
            Schema::table('agendamentos', function (Blueprint $table) {
                if (Schema::hasColumn('agendamentos', 'paciente_id')) {
                    if (Schema::hasColumn('agendamentos', 'patient_id')) {
                        try { $table->dropForeign(['patient_id']); } catch (\Throwable $e) {}
                        try { $table->dropIndex(['patient_id']); } catch (\Throwable $e) {}
                        $table->dropColumn('patient_id');
                    }
                }
            });
        }
    }

    public function down(): void
    {
        // revert column renames
        if (Schema::hasTable('prontuarios')) {
            Schema::table('prontuarios', function (Blueprint $table) {
                if (Schema::hasColumn('prontuarios', 'paciente_id')) {
                    try { $table->dropForeign(['paciente_id']); } catch (\Throwable $e) {}
                    $table->renameColumn('paciente_id', 'patient_id');
                    $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
                }
            });
        }

        if (Schema::hasTable('agendamentos')) {
            Schema::table('agendamentos', function (Blueprint $table) {
                if (Schema::hasColumn('agendamentos', 'paciente_id')) {
                    try { $table->dropForeign(['paciente_id']); } catch (\Throwable $e) {}
                    $table->renameColumn('paciente_id', 'patient_id');
                    $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
                }
            });
        }

        if (Schema::hasTable('pacientes') && !Schema::hasTable('patients')) {
            Schema::rename('pacientes', 'patients');
        }

        if (Schema::hasTable('agendamentos') && !Schema::hasTable('appointments')) {
            Schema::rename('agendamentos', 'appointments');
        }
    }
};
