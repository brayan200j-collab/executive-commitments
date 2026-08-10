<?php

namespace App\Filament\Resources\Commitments\Schemas;

use App\Enums\CommitmentPriority;
use App\Enums\CommitmentStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommitmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Compromiso')
                    ->columns(2)
                    ->components([
                        TextInput::make('code')
                            ->label('Código')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit')
                            ->columnSpanFull()
                            ->helperText('Se genera automáticamente al crear el compromiso.'),
                        Select::make('meeting_id')
                            ->label('Reunión de origen')
                            ->relationship('meeting', 'title')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Select::make('responsible_id')
                            ->label('Responsable')
                            ->relationship('responsible', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('due_date')
                            ->label('Fecha límite')
                            ->required()
                            ->native(false),
                        Select::make('priority')
                            ->label('Prioridad')
                            ->options(CommitmentPriority::class)
                            ->default(CommitmentPriority::Media)
                            ->required(),
                        Select::make('status')
                            ->label('Estado')
                            ->options(CommitmentStatus::class)
                            ->default(CommitmentStatus::Pendiente)
                            ->required(),
                        TextInput::make('progress_percentage')
                            ->label('Porcentaje de avance')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->required(),
                        Textarea::make('evidence')
                            ->label('Evidencia u observaciones')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
