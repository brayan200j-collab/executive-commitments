<?php

namespace App\Services\ExecutiveSummary\Exceptions;

use RuntimeException;

/**
 * Cualquier fallo al generar el resumen con un proveedor externo
 * (red, credenciales, formato de respuesta inesperado) se normaliza a
 * esta excepcion, que es lo unico que FallbackExecutiveSummaryService
 * necesita conocer para decidir si cae al motor local.
 */
class ExecutiveSummaryGenerationException extends RuntimeException {}
