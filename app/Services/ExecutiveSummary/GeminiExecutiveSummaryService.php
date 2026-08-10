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
 * Implementacion externa alternativa (no conectada por defecto, ver
 * OpenAiExecutiveSummaryService): redacta el resumen ejecutivo con la
 * API de Gemini (Google AI Studio). Se deja aqui, probada y funcional,
 * como evidencia de que ExecutiveSummaryServiceInterface realmente
 * permite intercambiar de proveedor sin tocar el resto del sistema.
 */
class GeminiExecutiveSummaryService implements ExecutiveSummaryServiceInterface
{
    use BuildsExecutiveSummaryPrompt;

    private const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';

    public function __construct(
        private readonly DashboardMetricsService $metrics,
        private readonly string $apiKey,
        private readonly string $model,
    ) {}

    public function generate(): ExecutiveSummaryResult
    {
        try {
            $response = Http::timeout(15)
                ->retry(1, 200)
                ->post(sprintf(self::ENDPOINT, $this->model).'?key='.$this->apiKey, [
                    'contents' => [
                        ['parts' => [['text' => $this->buildExecutiveSummaryPrompt($this->metrics)]]],
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'responseSchema' => $this->geminiSchema(),
                    ],
                ]);
        } catch (Throwable $e) {
            throw new ExecutiveSummaryGenerationException('No se pudo contactar a Gemini: '.$e->getMessage(), previous: $e);
        }

        if ($response->failed()) {
            throw new ExecutiveSummaryGenerationException(
                "Gemini respondio con error {$response->status()}: ".$response->body(),
            );
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');

        if (blank($text)) {
            throw new ExecutiveSummaryGenerationException('Gemini devolvio una respuesta vacia.');
        }

        $decoded = json_decode($text, true);

        if (! $this->isValidExecutiveSummaryPayload($decoded)) {
            throw new ExecutiveSummaryGenerationException('Gemini devolvio un formato inesperado.');
        }

        return new ExecutiveSummaryResult(
            providerName: "Google Gemini ({$this->model})",
            generatedAt: CarbonImmutable::now(),
            headline: (string) $decoded['headline'],
            body: (string) $decoded['body'],
            highlights: array_values(array_map('strval', (array) $decoded['highlights'])),
        );
    }

    /**
     * Gemini usa mayusculas en los tipos del schema (OBJECT/STRING/ARRAY),
     * a diferencia del JSON Schema estandar que usa el resto de proveedores.
     *
     * @return array<string, mixed>
     */
    private function geminiSchema(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'headline' => ['type' => 'STRING'],
                'body' => ['type' => 'STRING'],
                'highlights' => ['type' => 'ARRAY', 'items' => ['type' => 'STRING']],
            ],
            'required' => ['headline', 'body', 'highlights'],
        ];
    }
}
