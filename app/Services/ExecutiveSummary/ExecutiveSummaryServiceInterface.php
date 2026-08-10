<?php

namespace App\Services\ExecutiveSummary;

use App\Services\ExecutiveSummary\DTO\ExecutiveSummaryResult;

/**
 * Contrato del "Desafio de arquitectura para IA" de la prueba tecnica.
 *
 * Hoy solo existe LocalExecutiveSummaryService (reglas sobre datos reales,
 * sin red). El dia que se conecte un proveedor externo (OpenAI u otro),
 * la nueva clase solo debe implementar esta interfaz y rebindearse en
 * AppServiceProvider: nada en Filament, en las Pages ni en los Widgets
 * cambia. Ver DECISIONS.md, pregunta "Como conectarias OpenAI".
 */
interface ExecutiveSummaryServiceInterface
{
    public function generate(): ExecutiveSummaryResult;
}
