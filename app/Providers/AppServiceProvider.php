<?php

namespace App\Providers;

use App\Models\Commitment;
use App\Models\Risk;
use App\Observers\CommitmentObserver;
use App\Observers\RiskObserver;
use App\Services\Dashboard\DashboardMetricsService;
use App\Services\ExecutiveSummary\ExecutiveSummaryServiceInterface;
use App\Services\ExecutiveSummary\FallbackExecutiveSummaryService;
use App\Services\ExecutiveSummary\GeminiExecutiveSummaryService;
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
        // activo. Sin GEMINI_API_KEY en .env, se comporta exactamente
        // igual que antes (solo el motor local). Con la key definida,
        // intenta Gemini primero y cae al motor local si falla
        // (FallbackExecutiveSummaryService), sin tocar nada en Filament.
        $this->app->bind(ExecutiveSummaryServiceInterface::class, function ($app) {
            $local = $app->make(LocalExecutiveSummaryService::class);

            $apiKey = config('services.gemini.key');

            if (blank($apiKey)) {
                return $local;
            }

            $gemini = new GeminiExecutiveSummaryService(
                metrics: $app->make(DashboardMetricsService::class),
                apiKey: $apiKey,
                model: config('services.gemini.model'),
            );

            return new FallbackExecutiveSummaryService(primary: $gemini, fallback: $local);
        });
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
