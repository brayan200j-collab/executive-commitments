<?php

namespace Tests\Unit;

use App\Services\Dashboard\DashboardMetricsService;
use App\Services\ExecutiveSummary\Exceptions\ExecutiveSummaryGenerationException;
use App\Services\ExecutiveSummary\GeminiExecutiveSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiExecutiveSummaryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_parses_a_successful_gemini_response_into_the_shared_dto(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [[
                        'text' => json_encode([
                            'headline' => 'Proyecto en buen curso',
                            'body' => 'Resumen de prueba generado por Gemini.',
                            'highlights' => ['Alerta 1', 'Alerta 2'],
                        ]),
                    ]]],
                ]],
            ], 200),
        ]);

        $service = new GeminiExecutiveSummaryService(
            metrics: app(DashboardMetricsService::class),
            apiKey: 'fake-key',
            model: 'gemini-flash-latest',
        );

        $summary = $service->generate();

        $this->assertSame('Proyecto en buen curso', $summary->headline);
        $this->assertSame(['Alerta 1', 'Alerta 2'], $summary->highlights);
        $this->assertStringContainsString('Gemini', $summary->providerName);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'gemini-flash-latest:generateContent')
            && str_contains($request->url(), 'key=fake-key'));
    }

    public function test_throws_when_gemini_responds_with_an_error_status(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => 'invalid key'], 401),
        ]);

        $this->expectException(ExecutiveSummaryGenerationException::class);

        (new GeminiExecutiveSummaryService(app(DashboardMetricsService::class), 'bad-key', 'gemini-flash-latest'))
            ->generate();
    }

    public function test_throws_when_gemini_responds_with_an_unexpected_shape(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'esto no es json valido']]]]],
            ], 200),
        ]);

        $this->expectException(ExecutiveSummaryGenerationException::class);

        (new GeminiExecutiveSummaryService(app(DashboardMetricsService::class), 'fake-key', 'gemini-flash-latest'))
            ->generate();
    }
}
