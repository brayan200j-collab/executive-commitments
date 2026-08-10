<?php

namespace App\Filament\Resources\Meetings\Schemas;

use App\Enums\MeetingStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MeetingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Reunión')
                    ->columns(2)
                    ->components([
                        TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        DatePicker::make('date')
                            ->label('Fecha')
                            ->required()
                            ->native(false),
                        TextInput::make('organization')
                            ->label('Organización')
                            ->required()
                            ->maxLength(255),
                        Select::make('responsible_id')
                            ->label('Responsable')
                            ->relationship('responsible', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('status')
                            ->label('Estado')
                            ->options(MeetingStatus::class)
                            ->default(MeetingStatus::Programada)
                            ->required(),
                    ]),
            ]);
    }
}
