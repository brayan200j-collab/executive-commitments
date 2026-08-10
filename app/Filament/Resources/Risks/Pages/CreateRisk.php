<?php

namespace App\Filament\Resources\Risks\Pages;

use App\Actions\Risks\GenerateRiskCodeAction;
use App\Filament\Resources\Risks\RiskResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRisk extends CreateRecord
{
    protected static string $resource = RiskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['code'] = app(GenerateRiskCodeAction::class)();

        return $data;
    }
}
