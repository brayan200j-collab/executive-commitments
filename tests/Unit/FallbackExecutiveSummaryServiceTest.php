<?php

namespace Tests\Unit;

use App\Services\ExecutiveSummary\DTO\ExecutiveSummaryResult;
use App\Services\ExecutiveSummary\ExecutiveSummaryServiceInterface;
use App\Services\ExecutiveSummary\FallbackExecutiveSummaryService;
use Carbon\CarbonImmutable;
use RuntimeException;
use Tests\TestCase;

class FallbackExecutiveSummaryServiceTest extends TestCase
{
    public function test_uses_the_primary_provider_when_it_succeeds(): void
    {
        $primary = $this->fakeProvider('primario');
        $fallback = $this->fakeProvider('secundario');

        $result = (new FallbackExecutiveSummaryService($primary, $fallback))->generate();

        $this->assertSame('primario', $result->providerName);
    }

    public function test_falls_back_when_the_primary_provider_throws(): void
    {
        $primary = new class implements ExecutiveSummaryServiceInterface
        {
            public function generate(): ExecutiveSummaryResult
            {
                throw new RuntimeException('Gemini no disponible');
            }
        };
        $fallback = $this->fakeProvider('secundario');

        $result = (new FallbackExecutiveSummaryService($primary, $fallback))->generate();

        $this->assertSame('secundario', $result->providerName);
    }

    private function fakeProvider(string $name): ExecutiveSummaryServiceInterface
    {
        return new class($name) implements ExecutiveSummaryServiceInterface
        {
            public function __construct(private readonly string $name) {}

            public function generate(): ExecutiveSummaryResult
            {
                return new ExecutiveSummaryResult(
                    providerName: $this->name,
                    generatedAt: CarbonImmutable::now(),
                    headline: 'headline',
                    body: 'body',
                    highlights: [],
                );
            }
        };
    }
}
