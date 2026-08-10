<?php

namespace App\Actions\Risks;

use App\Actions\Support\GeneratesSequentialCode;
use App\Models\Risk;

class GenerateRiskCodeAction
{
    use GeneratesSequentialCode;

    public function __invoke(): string
    {
        return $this->nextCode(Risk::class, 'RIS-');
    }
}
