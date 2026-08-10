<?php

namespace App\Filament\Resources\Risks\Schemas;

use App\Enums\RiskImpact;
use App\Enums\RiskProbability;
use App\Enums\RiskStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RiskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Riesgo')
                    ->columns(2)
                    ->components([
                        TextInput::make('code')
                            ->label('Código')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit')
                            ->columnSpanFull()
                            ->helperText('Se genera automáticamente al crear el riesgo.'),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Select::make('probability')
                            ->label('Probabilidad')
                            ->options(RiskProbability::class)
                            ->default(RiskProbability::Media)
                            ->live()
                            ->required(),
                        Select::make('impact')
                            ->label('Impacto')
                            ->options(RiskImpact::class)
                            ->default(RiskImpact::Medio)
                            ->live()
                            ->required(),
                        TextInput::make('level')
                            ->label('Nivel (calculado)')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit')
                            ->formatStateUsing(fn ($state) => $state?->getLabel())
                            ->helperText('Se recalcula automáticamente a partir de probabilidad e impacto.'),
                        Select::make('responsible_id')
                            ->label('Responsable')
                            ->relationship('responsible', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('status')
                            ->label('Estado')
                            ->options(RiskStatus::class)
                            ->default(RiskStatus::Activo)
                            ->required(),
                    ]),
            ]);
    }
}
