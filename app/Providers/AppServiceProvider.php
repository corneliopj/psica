<?php

namespace App\Providers;

use App\Contracts\AgendamentoServiceContract;
use App\Contracts\AuditoriaServiceContract;
use App\Contracts\DisponibilidadeServiceContract;
use App\Contracts\NotificacaoServiceContract;
use App\Contracts\ProntuarioServiceContract;
use App\Services\AgendamentoService;
use App\Services\AuditoriaService;
use App\Services\DisponibilidadeService;
use App\Services\NotificacaoService;
use App\Services\ProntuarioService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AgendamentoServiceContract::class, AgendamentoService::class);
        $this->app->bind(DisponibilidadeServiceContract::class, DisponibilidadeService::class);
        $this->app->bind(ProntuarioServiceContract::class, ProntuarioService::class);
        $this->app->bind(AuditoriaServiceContract::class, AuditoriaService::class);
        $this->app->bind(NotificacaoServiceContract::class, NotificacaoService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
