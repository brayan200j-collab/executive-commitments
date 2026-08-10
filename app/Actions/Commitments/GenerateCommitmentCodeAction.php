<?php

namespace App\Actions\Commitments;

use App\Actions\Support\GeneratesSequentialCode;
use App\Models\Commitment;

class GenerateCommitmentCodeAction
{
    use GeneratesSequentialCode;

    public function __invoke(): string
    {
        return $this->nextCode(Commitment::class, 'COM-');
    }
}
