<?php

namespace App\Actions\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Genera codigos correlativos con prefijo (ej. COM-0001, RIS-0001).
 *
 * El ultimo codigo se lee bloqueando la fila mas reciente (lockForUpdate)
 * dentro de una transaccion, para evitar codigos duplicados si dos usuarios
 * crean registros al mismo tiempo. En SQLite el bloqueo de fila no existe
 * realmente (la escritura ya serializa toda la base de datos), pero la
 * transaccion deja el codigo listo para motores con locking real (MySQL/
 * Postgres) sin cambiar esta clase. Ver DECISIONS.md para la alternativa
 * de tabla de secuencias dedicada a gran escala.
 */
trait GeneratesSequentialCode
{
    private function nextCode(string $modelClass, string $prefix, int $padLength = 4): string
    {
        /** @var class-string<Model> $modelClass */
        return DB::transaction(function () use ($modelClass, $prefix, $padLength): string {
            $lastCode = $modelClass::query()
                ->where('code', 'like', $prefix.'%')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->value('code');

            $lastNumber = $lastCode ? (int) Str::after($lastCode, $prefix) : 0;

            return $prefix.str_pad((string) ($lastNumber + 1), $padLength, '0', STR_PAD_LEFT);
        });
    }
}
