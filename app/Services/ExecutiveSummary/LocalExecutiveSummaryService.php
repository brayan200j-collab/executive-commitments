<?php

namespace App\Services\ExecutiveSummary;

use App\Services\Dashboard\DashboardMetricsService;
use App\Services\ExecutiveSummary\DTO\ExecutiveSummaryResult;
use Carbon\CarbonImmutable;

/**
 * Implementacion "local": redacta el resumen a partir de reglas fijas
 * sobre los datos reales de la base de datos, sin llamar a ningun
 * proveedor externo. Sirve como reemplazo funcional mientras no hay
 * presupuesto/credenciales de un LLM, y como contrato de referencia para
 * cuando lo haya.
 */
class LocalExecutiveSummaryService implements ExecutiveSummaryServiceInterface
{
    public function __construct(
        private readonly DashboardMetricsService $metrics,
    ) {}

    public function generate(): ExecutiveSummaryResult
    {
        $total = $this->metrics->totalCommitments();
        $completed = $this->metrics->completedCommitments();
        $overdue = $this->metrics->overdueCommitments();
        $dueSoon = $this->metrics->dueSoonCommitments();
        $activeRisks = $this->metrics->activeRisks();
        $byPriority = $this->metrics->commitmentsByPriority();

        $completionRate = $total > 0 ? round(($completed / $total) * 100) : 0;
        $critical = $byPriority->get('Crítica', 0);

        $body = $total > 0
            ? sprintf(
                'De %d compromisos registrados, %d están cumplidos (%d%% de avance general). '.
                '%d compromisos se encuentran vencidos y requieren atención inmediata, y %d vencen '.
                'en los próximos 7 días. Actualmente hay %d riesgos activos bajo monitoreo, de los '.
                'cuales %d compromisos tienen prioridad crítica.',
                $total,
                $completed,
                $completionRate,
                $overdue,
                $dueSoon,
                $activeRisks,
                $critical,
            )
            : 'Todavía no hay compromisos registrados en el sistema.';

        $highlights = array_values(array_filter([
            $overdue > 0 ? "{$overdue} compromiso(s) vencido(s) sin cumplir." : null,
            $dueSoon > 0 ? "{$dueSoon} compromiso(s) vencen en los próximos 7 días." : null,
            $critical > 0 ? "{$critical} compromiso(s) de prioridad crítica en curso." : null,
            $activeRisks > 0 ? "{$activeRisks} riesgo(s) activo(s) requieren seguimiento." : null,
        ]));

        if ($highlights === []) {
            $highlights[] = 'Sin alertas relevantes: el proyecto está al día.';
        }

        return new ExecutiveSummaryResult(
            providerName: 'Motor local (reglas sobre datos en tiempo real)',
            generatedAt: CarbonImmutable::now(),
            headline: "Resumen ejecutivo · {$completionRate}% de avance general",
            body: $body,
            highlights: $highlights,
        );
    }
}
