<?php

namespace App\Providers;

use App\Models\Commitment;
use App\Models\Risk;
use App\Observers\CommitmentObserver;
use App\Observers\RiskObserver;
use App\Services\ExecutiveSummary\ExecutiveSummaryServiceInterface;
use App\Services\ExecutiveSummary\LocalExecutiveSummaryService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Unico lugar que decide que proveedor de resumen ejecutivo esta
        // activo. Cambiar a un proveedor externo es reemplazar esta linea.
        $this->app->bind(ExecutiveSummaryServiceInterface::class, LocalExecutiveSummaryService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Commitment::observe(CommitmentObserver::class);
        Risk::observe(RiskObserver::class);
    }
}
