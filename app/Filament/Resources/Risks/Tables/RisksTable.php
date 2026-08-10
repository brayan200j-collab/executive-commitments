<?php

namespace App\Filament\Resources\Risks\Tables;

use App\Enums\RiskImpact;
use App\Enums\RiskLevel;
use App\Enums\RiskProbability;
use App\Enums\RiskStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RisksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('probability')
                    ->label('Probabilidad')
                    ->badge(),
                TextColumn::make('impact')
                    ->label('Impacto')
                    ->badge(),
                TextColumn::make('level')
                    ->label('Nivel')
                    ->badge()
                    ->sortable(),
                TextColumn::make('responsible.name')
                    ->label('Responsable')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('level')
                    ->label('Nivel')
                    ->options(RiskLevel::class),
                SelectFilter::make('probability')
                    ->label('Probabilidad')
                    ->options(RiskProbability::class),
                SelectFilter::make('impact')
                    ->label('Impacto')
                    ->options(RiskImpact::class),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(RiskStatus::class),
            ])
            ->defaultSort('code')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
