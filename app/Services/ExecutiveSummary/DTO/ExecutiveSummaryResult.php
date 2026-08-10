<?php

namespace App\Services\ExecutiveSummary\DTO;

use Carbon\CarbonImmutable;

/**
 * Forma de salida estable independiente del proveedor que la genere
 * (motor local basado en reglas hoy, un LLM externo manana). Ningun
 * consumidor de este DTO deberia saber como se calculo su contenido.
 */
final readonly class ExecutiveSummaryResult
{
    /**
     * @param  array<int, string>  $highlights
     */
    public function __construct(
        public string $providerName,
        public CarbonImmutable $generatedAt,
        public string $headline,
        public string $body,
        public array $highlights,
    ) {}
}
