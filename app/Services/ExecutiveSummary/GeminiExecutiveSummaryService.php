<?php

namespace App\Services\ExecutiveSummary;

use App\Services\Dashboard\DashboardMetricsService;
use App\Services\ExecutiveSummary\DTO\ExecutiveSummaryResult;
use App\Services\ExecutiveSummary\Exceptions\ExecutiveSummaryGenerationException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Implementacion externa del "Desafio de arquitectura para IA": redacta
 * el resumen ejecutivo con la API de Gemini (Google AI Studio), a partir
 * de los mismos datos que ya calcula DashboardMetricsService (no se
 * duplican consultas). No conoce Filament ni el Dashboard: solo cumple
 * ExecutiveSummaryServiceInterface, igual que LocalExecutiveSummaryService.
 */
class GeminiExecutiveSummaryService implements ExecutiveSummaryServiceInterface
{
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
                        ['parts' => [['text' => $this->buildPrompt()]]],
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'responseSchema' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'headline' => ['type' => 'STRING'],
                                'body' => ['type' => 'STRING'],
                                'highlights' => ['type' => 'ARRAY', 'items' => ['type' => 'STRING']],
                            ],
                            'required' => ['headline', 'body', 'highlights'],
                        ],
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

        if (! is_array($decoded) || ! isset($decoded['headline'], $decoded['body'], $decoded['highlights'])) {
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

    private function buildPrompt(): string
    {
        $total = $this->metrics->totalCommitments();
        $completed = $this->metrics->completedCommitments();
        $overdue = $this->metrics->overdueCommitments();
        $dueSoon = $this->metrics->dueSoonCommitments();
        $activeRisks = $this->metrics->activeRisks();
        $byStatus = $this->metrics->commitmentsByStatus();
        $byPriority = $this->metrics->commitmentsByPriority();

        return <<<PROMPT
            Eres un asistente que redacta resúmenes ejecutivos corporativos en español,
            para directivos que no tienen tiempo de revisar el detalle. Con los siguientes
            datos reales de seguimiento de proyecto, responde EXCLUSIVAMENTE con el JSON
            pedido por el schema (headline, body, highlights). No inventes cifras que no
            aparezcan aquí.

            Total de compromisos: {$total}
            Compromisos cumplidos: {$completed}
            Compromisos vencidos: {$overdue}
            Compromisos que vencen en los próximos 7 días: {$dueSoon}
            Riesgos activos: {$activeRisks}
            Compromisos por estado: {$byStatus->toJson()}
            Compromisos por prioridad: {$byPriority->toJson()}

            headline: una frase corta (máx. 12 palabras) con el estado general.
            body: 2-4 frases en tono ejecutivo explicando la situación.
            highlights: entre 1 y 4 alertas o datos que un directivo debería notar primero.
            PROMPT;
    }
}
