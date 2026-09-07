<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('deploy:finalize {--skip-migrate : Nao executa migrate}', function () {
    $steps = [];

    if (! $this->option('skip-migrate')) {
        $steps[] = ['migrate', ['--force' => true]];
    }

    $steps[] = ['optimize:clear', []];
    $steps[] = ['config:cache', []];
    $steps[] = ['route:cache', []];
    $steps[] = ['view:cache', []];

    foreach ($steps as [$command, $arguments]) {
        $this->line("Executando: php artisan {$command}");

        $exitCode = Artisan::call($command, $arguments);
        $output = trim(Artisan::output());

        if ($output !== '') {
            $this->line($output);
        }

        if ($exitCode !== 0) {
            $this->error("Falha no comando {$command} (exit code {$exitCode}).");
            return 1;
        }
    }

    $this->info('Deploy finalizado com sucesso.');

    return 0;
})->purpose('Executa migrate e rebuild de caches para deploy');
