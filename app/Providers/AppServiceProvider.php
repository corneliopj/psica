<?php

namespace App\Providers;

use App\Contracts\AgendamentoServiceContract;
use App\Contracts\AuditoriaServiceContract;
use App\Contracts\DisponibilidadeServiceContract;
use App\Contracts\FaturaServiceContract;
use App\Contracts\NotificacaoServiceContract;
use App\Contracts\ProntuarioServiceContract;
use App\Contracts\ReciboServiceContract;
use App\Services\AgendamentoService;
use App\Services\AuditoriaService;
use App\Services\DisponibilidadeService;
use App\Services\FaturaService;
use App\Services\NotificacaoService;
use App\Services\ProntuarioService;
use App\Services\ReciboService;
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
        $this->app->bind(FaturaServiceContract::class, FaturaService::class);
        $this->app->bind(ReciboServiceContract::class, ReciboService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
