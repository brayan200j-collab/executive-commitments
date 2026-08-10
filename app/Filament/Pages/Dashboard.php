<?php

namespace App\Filament\Pages;

use App\Services\ExecutiveSummary\ExecutiveSummaryServiceInterface;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    /**
     * "Desafio de arquitectura para IA": la pagina solo pide el resultado
     * al contrato ExecutiveSummaryServiceInterface. No sabe (ni le
     * importa) si detras esta el motor local o, en el futuro, un
     * proveedor externo.
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateExecutiveSummary')
                ->label('Generar resumen ejecutivo')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->modalHeading('Resumen ejecutivo')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Cerrar')
                ->modalContent(fn () => view('filament.executive-summary-modal', [
                    'summary' => app(ExecutiveSummaryServiceInterface::class)->generate(),
                ])),
        ];
    }
}
