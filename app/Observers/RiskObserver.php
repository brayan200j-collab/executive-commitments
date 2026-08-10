<?php

namespace App\Observers;

use App\Actions\Risks\GenerateRiskCodeAction;
use App\Models\Risk;
use App\Services\Risks\RiskLevelResolver;

/**
 * Regla obligatoria #2 de la prueba: un riesgo con probabilidad y
 * impacto altos se clasifica automaticamente como critico. Se recalcula
 * aqui -no en el formulario- para que el nivel nunca quede desincronizado
 * de la matriz definida en RiskLevelResolver (unica fuente de verdad).
 */
class RiskObserver
{
    public function __construct(
        private readonly RiskLevelResolver $resolver,
        private readonly GenerateRiskCodeAction $generateCode,
    ) {}

    /**
     * Ver CommitmentObserver::creating() para la razon de asignar el
     * codigo aqui y no en la factory: evita colisiones al crear en lote.
     */
    public function creating(Risk $risk): void
    {
        if (blank($risk->code)) {
            $risk->code = ($this->generateCode)();
        }
    }

    public function saving(Risk $risk): void
    {
        if ($risk->exists && ! $risk->isDirty(['probability', 'impact'])) {
            return;
        }

        $risk->level = $this->resolver->resolve($risk->probability, $risk->impact)->value;
    }
}
