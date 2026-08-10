<?php

namespace App\Services\ExecutiveSummary;

use App\Services\ExecutiveSummary\DTO\ExecutiveSummaryResult;
use Throwable;

/**
 * Decorador sobre ExecutiveSummaryServiceInterface: intenta el proveedor
 * primario (ej. OpenAI) y, ante cualquier fallo (red, credenciales,
 * formato de respuesta), cae automaticamente al secundario sin que el
 * usuario del panel vea un error. El fallo se reporta a los logs para
 * que no quede invisible.
 */
class FallbackExecutiveSummaryService implements ExecutiveSummaryServiceInterface
{
    public function __construct(
        private readonly ExecutiveSummaryServiceInterface $primary,
        private readonly ExecutiveSummaryServiceInterface $fallback,
    ) {}

    public function generate(): ExecutiveSummaryResult
    {
        try {
            return $this->primary->generate();
        } catch (Throwable $e) {
            report($e);

            return $this->fallback->generate();
        }
    }
}
