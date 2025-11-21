<?php

namespace App\Filament\RelationManagers;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
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
                    ->placeholder('Instalación de equipo principal')
                    ->required(),
                Textarea::make('comment')
                    ->label('Comentario')
                    ->placeholder('Se inició la instalación del equipo principal en el proyecto')
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
            ->components([
                TextEntry::make('title')
                    ->label('Título'),
                TextEntry::make('comment')
                    ->label('Comentario'),
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
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),
                TextColumn::make('comment')
                    ->label('Comentario')
                    ->searchable()
                    ->formatStateUsing(
                        fn(string $state) => str($state)
                            ->limit(limit: 70, preserveWords: true)
                    ),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->hidden(!currentUserHasPermission('relationships.activities.create')),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hidden(!currentUserHasPermission('relationships.activities.show')),
                    EditAction::make()->hidden(!currentUserHasPermission('relationships.activities.edit')),
                    DeleteAction::make()->hidden(!currentUserHasPermission('relationships.activities.delete')),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->hidden(!currentUserHasPermission('relationships.activities.delete')),
                ]),
            ]);
    }
}
