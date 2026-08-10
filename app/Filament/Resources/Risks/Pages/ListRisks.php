<?php

namespace App\Filament\Resources\Risks\Pages;

use App\Filament\Resources\Risks\RiskResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRisks extends ListRecords
{
    protected static string $resource = RiskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
