<?php

namespace App\Providers;

use App\Models\Commitment;
use App\Models\Risk;
use App\Observers\CommitmentObserver;
use App\Observers\RiskObserver;
use App\Services\Dashboard\DashboardMetricsService;
use App\Services\ExecutiveSummary\ExecutiveSummaryServiceInterface;
use App\Services\ExecutiveSummary\FallbackExecutiveSummaryService;
use App\Services\ExecutiveSummary\LocalExecutiveSummaryService;
use App\Services\ExecutiveSummary\OpenAiExecutiveSummaryService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Unico lugar que decide que proveedor de resumen ejecutivo esta
        // activo. Sin OPENAI_API_KEY en .env, se comporta exactamente
        // igual que con solo el motor local. Con la key definida,
        // intenta OpenAI primero y cae al motor local si falla
        // (FallbackExecutiveSummaryService), sin tocar nada en Filament.
        $this->app->bind(ExecutiveSummaryServiceInterface::class, function ($app) {
            $local = $app->make(LocalExecutiveSummaryService::class);

            $apiKey = config('services.openai.key');

            if (blank($apiKey)) {
                return $local;
            }

            $openAi = new OpenAiExecutiveSummaryService(
                metrics: $app->make(DashboardMetricsService::class),
                apiKey: $apiKey,
                model: config('services.openai.model'),
                organization: config('services.openai.organization'),
            );

            return new FallbackExecutiveSummaryService(primary: $openAi, fallback: $local);
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
