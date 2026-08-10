<?php

namespace Tests\Unit;

use App\Services\Dashboard\DashboardMetricsService;
use App\Services\ExecutiveSummary\Exceptions\ExecutiveSummaryGenerationException;
use App\Services\ExecutiveSummary\OpenAiExecutiveSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenAiExecutiveSummaryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_parses_a_successful_openai_response_into_the_shared_dto(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'headline' => 'Proyecto en buen curso',
                            'body' => 'Resumen de prueba generado por OpenAI.',
                            'highlights' => ['Alerta 1', 'Alerta 2'],
                        ]),
                    ],
                ]],
            ], 200),
        ]);

        $service = new OpenAiExecutiveSummaryService(
            metrics: app(DashboardMetricsService::class),
            apiKey: 'fake-key',
            model: 'gpt-4o-mini',
        );

        $summary = $service->generate();

        $this->assertSame('Proyecto en buen curso', $summary->headline);
        $this->assertSame(['Alerta 1', 'Alerta 2'], $summary->highlights);
        $this->assertStringContainsString('OpenAI', $summary->providerName);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'api.openai.com/v1/chat/completions')
            && $request->hasHeader('Authorization', 'Bearer fake-key')
            && $request['model'] === 'gpt-4o-mini');
    }

    public function test_sends_the_organization_header_when_configured(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [['message' => ['content' => json_encode([
                    'headline' => 'h', 'body' => 'b', 'highlights' => [],
                ])]]],
            ], 200),
        ]);

        (new OpenAiExecutiveSummaryService(app(DashboardMetricsService::class), 'fake-key', 'gpt-4o-mini', 'org-123'))
            ->generate();

        Http::assertSent(fn ($request) => $request->hasHeader('OpenAI-Organization', 'org-123'));
    }

    public function test_throws_when_openai_responds_with_an_error_status(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response(['error' => ['message' => 'invalid key']], 401),
        ]);

        $this->expectException(ExecutiveSummaryGenerationException::class);

        (new OpenAiExecutiveSummaryService(app(DashboardMetricsService::class), 'bad-key', 'gpt-4o-mini'))
            ->generate();
    }

    public function test_throws_when_openai_responds_with_an_unexpected_shape(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [['message' => ['content' => 'esto no es json valido']]],
            ], 200),
        ]);

        $this->expectException(ExecutiveSummaryGenerationException::class);

        (new OpenAiExecutiveSummaryService(app(DashboardMetricsService::class), 'fake-key', 'gpt-4o-mini'))
            ->generate();
    }
}
