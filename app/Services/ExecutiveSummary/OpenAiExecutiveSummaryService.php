<?php

namespace App\Services\ExecutiveSummary;

use App\Services\Dashboard\DashboardMetricsService;
use App\Services\ExecutiveSummary\Concerns\BuildsExecutiveSummaryPrompt;
use App\Services\ExecutiveSummary\DTO\ExecutiveSummaryResult;
use App\Services\ExecutiveSummary\Exceptions\ExecutiveSummaryGenerationException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Proveedor externo conectado por defecto para el "Desafio de
 * arquitectura para IA" (Chat Completions API de OpenAI, con salida
 * JSON estructurada via response_format: json_schema). Reusa los datos
 * de DashboardMetricsService y no conoce Filament ni el Dashboard: solo
 * cumple ExecutiveSummaryServiceInterface, igual que
 * LocalExecutiveSummaryService.
 */
class OpenAiExecutiveSummaryService implements ExecutiveSummaryServiceInterface
{
    use BuildsExecutiveSummaryPrompt;

    private const ENDPOINT = 'https://api.openai.com/v1/chat/completions';

    public function __construct(
        private readonly DashboardMetricsService $metrics,
        private readonly string $apiKey,
        private readonly string $model,
        private readonly ?string $organization = null,
    ) {}

    public function generate(): ExecutiveSummaryResult
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->when($this->organization, fn ($http) => $http->withHeaders([
                    'OpenAI-Organization' => $this->organization,
                ]))
                ->timeout(20)
                ->retry(1, 200)
                ->post(self::ENDPOINT, [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Redactas resúmenes ejecutivos corporativos en español, en formato JSON.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $this->buildExecutiveSummaryPrompt($this->metrics),
                        ],
                    ],
                    'response_format' => [
                        'type' => 'json_schema',
                        'json_schema' => [
                            'name' => 'executive_summary',
                            'strict' => true,
                            'schema' => $this->executiveSummaryJsonSchema(),
                        ],
                    ],
                ]);
        } catch (Throwable $e) {
            throw new ExecutiveSummaryGenerationException('No se pudo contactar a OpenAI: '.$e->getMessage(), previous: $e);
        }

        if ($response->failed()) {
            throw new ExecutiveSummaryGenerationException(
                "OpenAI respondio con error {$response->status()}: ".$response->body(),
            );
        }

        $content = data_get($response->json(), 'choices.0.message.content');

        if (blank($content)) {
            throw new ExecutiveSummaryGenerationException('OpenAI devolvio una respuesta vacia.');
        }

        $decoded = json_decode($content, true);

        if (! $this->isValidExecutiveSummaryPayload($decoded)) {
            throw new ExecutiveSummaryGenerationException('OpenAI devolvio un formato inesperado.');
        }

        return new ExecutiveSummaryResult(
            providerName: "OpenAI ({$this->model})",
            generatedAt: CarbonImmutable::now(),
            headline: (string) $decoded['headline'],
            body: (string) $decoded['body'],
            highlights: array_values(array_map('strval', (array) $decoded['highlights'])),
        );
    }
}
