<?php

namespace Tests\Unit;

use App\Services\ExecutiveSummary\ExecutiveSummaryServiceInterface;
use App\Services\ExecutiveSummary\FallbackExecutiveSummaryService;
use App\Services\ExecutiveSummary\LocalExecutiveSummaryService;
use Tests\TestCase;

/**
 * Regresion: phpunit.xml fuerza GEMINI_API_KEY="" para que la suite
 * nunca dependa del .env real del desarrollador ni de red externa. Este
 * test fija el contrato del binding en AppServiceProvider para que un
 * futuro cambio ahi no rompa ese aislamiento sin que salte un test.
 */
class ExecutiveSummaryBindingTest extends TestCase
{
    public function test_resolves_to_local_only_when_no_gemini_key_is_configured(): void
    {
        config(['services.gemini.key' => null]);

        $service = app(ExecutiveSummaryServiceInterface::class);

        $this->assertInstanceOf(LocalExecutiveSummaryService::class, $service);
    }

    public function test_resolves_to_fallback_wrapping_gemini_when_a_key_is_configured(): void
    {
        config(['services.gemini.key' => 'a-key', 'services.gemini.model' => 'gemini-flash-latest']);

        $service = app(ExecutiveSummaryServiceInterface::class);

        $this->assertInstanceOf(FallbackExecutiveSummaryService::class, $service);
    }
}
