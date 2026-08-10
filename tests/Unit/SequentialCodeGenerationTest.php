<?php

namespace Tests\Unit;

use App\Models\Commitment;
use App\Models\Risk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresion: crear varios registros en lote via factory (count()->create())
 * arma primero N modelos en memoria y recien despues los persiste. Si el
 * codigo se asignara en un hook "afterMaking" de la factory, los N modelos
 * verian el mismo "ultimo codigo" en BD y colisionarian. El codigo debe
 * asignarse en el evento `creating` del modelo, que se dispara uno a la vez
 * justo antes de cada INSERT.
 */
class SequentialCodeGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_created_commitments_get_unique_sequential_codes(): void
    {
        $commitments = Commitment::factory()->count(5)->create();

        $codes = $commitments->pluck('code')->sort()->values()->all();

        $this->assertSame(
            ['COM-0001', 'COM-0002', 'COM-0003', 'COM-0004', 'COM-0005'],
            $codes,
        );
    }

    public function test_bulk_created_risks_get_unique_sequential_codes(): void
    {
        $risks = Risk::factory()->count(5)->create();

        $codes = $risks->pluck('code')->sort()->values()->all();

        $this->assertSame(
            ['RIS-0001', 'RIS-0002', 'RIS-0003', 'RIS-0004', 'RIS-0005'],
            $codes,
        );
    }
}
