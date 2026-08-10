<?php

namespace App\Services\ExecutiveSummary\Concerns;

use App\Services\Dashboard\DashboardMetricsService;

/**
 * Prompt compartido entre proveedores externos (Gemini, OpenAI, el que
 * siga). Vive en un solo lugar para que agregar un proveedor nuevo no
 * implique reescribir como se le explican los datos al modelo.
 */
trait BuildsExecutiveSummaryPrompt
{
    private function buildExecutiveSummaryPrompt(DashboardMetricsService $metrics): string
    {
        $total = $metrics->totalCommitments();
        $completed = $metrics->completedCommitments();
        $overdue = $metrics->overdueCommitments();
        $dueSoon = $metrics->dueSoonCommitments();
        $activeRisks = $metrics->activeRisks();
        $byStatus = $metrics->commitmentsByStatus();
        $byPriority = $metrics->commitmentsByPriority();

        return <<<PROMPT
            Eres un asistente que redacta resúmenes ejecutivos corporativos en español,
            para directivos que no tienen tiempo de revisar el detalle. Con los siguientes
            datos reales de seguimiento de proyecto, responde EXCLUSIVAMENTE con el JSON
            pedido (headline, body, highlights). No inventes cifras que no aparezcan aquí.

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

    /**
     * @return array{type: string, properties: array<string, mixed>, required: array<int, string>}
     */
    private function executiveSummaryJsonSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'headline' => ['type' => 'string'],
                'body' => ['type' => 'string'],
                'highlights' => ['type' => 'array', 'items' => ['type' => 'string']],
            ],
            'required' => ['headline', 'body', 'highlights'],
            'additionalProperties' => false,
        ];
    }

    /**
     * @param  array<string, mixed>  $decoded
     */
    private function isValidExecutiveSummaryPayload(mixed $decoded): bool
    {
        return is_array($decoded)
            && isset($decoded['headline'], $decoded['body'], $decoded['highlights'])
            && is_array($decoded['highlights']);
    }
}
