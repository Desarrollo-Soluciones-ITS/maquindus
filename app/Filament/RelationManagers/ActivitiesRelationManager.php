<?php

namespace App\Filament\RelationManagers;

use App\Filament\Filters\DateFilter;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = 'Actividades';

    protected static ?string $modelLabel = 'actividad';

    protected static ?string $pluralModelLabel = 'actividades';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Título')
                    ->placeholder('Ej. Instalación de equipo principal')
                    ->maxLength(80)
                    ->required(),
                Textarea::make('comment')
                    ->label('Comentario')
                    ->placeholder('Ej. Se inició la instalación del equipo principal en el proyecto')
                    ->maxLength(255)
                    ->required(),
                Select::make('people')
                    ->label('Participantes')
                    ->multiple()
                    ->relationship()
                    ->searchable(['name', 'surname', 'email'])
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->fullname . ' - ' . $record->email)
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextEntry::make('title')
                    ->label('Título'),
                TextEntry::make('comment')
                    ->label('Comentario'),
                TextEntry::make('created_at')
                    ->label('Fecha')
                    ->date('d/m/Y - g:i A')
                    ->timezone('America/Caracas'),
                RepeatableEntry::make('people')
                    ->label('Participantes')
                    ->grid(3)
                    ->columnSpanFull()
                    ->placeholder('N/A')
                    ->schema([
                        TextEntry::make('fullname')
                            ->label('Nombre'),
                        TextEntry::make('email')
                            ->label('Correo'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->filtersLayout(FiltersLayout::Modal)
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('comment')
                    ->label('Comentario')
                    ->searchable()
                    ->formatStateUsing(
                        fn(string $state) => str($state)
                            ->limit(limit: 70, preserveWords: true)
                    ),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->sortable()
                    ->date('d/m/Y - g:i A')
                    ->timezone('America/Caracas'),
            ])
            ->filters([
                DateFilter::make(),
            ])
            ->headerActions([
                CreateAction::make()->hidden(!currentUserHasPermission('relationships.activities.create')),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hidden(!currentUserHasPermission('relationships.activities.show')),
                    EditAction::make()->hidden(!currentUserHasPermission('relationships.activities.edit')),
                ])
            ]);
    }
}
