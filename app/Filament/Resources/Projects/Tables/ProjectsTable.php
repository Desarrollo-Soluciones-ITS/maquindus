<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Enums\Status;
use App\Filament\Actions\ArchiveAction;
use App\Filament\Filters\ArchivedFilter;
use App\Filament\Filters\DateFilter;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use App\Filament\Actions\EditAction;
use App\Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table; 

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start')
                    ->label('Fecha de inicio')
                    ->sortable()
                    ->date('d/m/Y')
                    ->timezone('America/Caracas'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn($state): string => match ($state) {
                        Status::Planning => 'primary',
                        Status::Ongoing => 'warning',
                        Status::Finished => 'success',
                        Status::Posible => 'secondary',
                        Status::awarded => 'danger',
                    }),
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->color(Color::Blue)
                    ->hidden(is_view_customer())
                    ->url(
                        fn($record) => !$record->customer_id ? null :
                        ViewCustomer::getUrl(['record' => $record->customer_id])
                    )
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->sortable(is_not_relation_manager())
                    ->date('d/m/Y - g:i A')
                    ->timezone('America/Caracas'),
            ])
            ->filters([
                DateFilter::make()
                    ->label('Fecha de carga'),
                DateFilter::make('start')
                    ->label('Fecha de inicio'),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(Status::options()),
                SelectFilter::make('customer')
                    ->label('Cliente')
                    ->relationship('customer', 'name'),
                ArchivedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hidden(!currentUserHasPermission('projects.show')),
                    EditAction::make()->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('projects.edit')),
                    ArchiveAction::make()->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('projects.delete')),
                    RestoreAction::make()->hidden(fn($record) => !$record->trashed() || !currentUserHasPermission('projects.restore')),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                ]),
                \Filament\Actions\Action::make('export')
                    ->label('Exportar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();
                        $projects = $query->get();
                        return \Maatwebsite\Excel\Facades\Excel::download(new class($projects) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                            protected $projects;
                            public function __construct($projects) { $this->projects = $projects; }
                            public function collection() { return $this->projects->map(function($project) {
                                return [
                                    'Código' => $project->code,
                                    'Nombre' => $project->name,
                                    'Fecha de inicio' => $project->start,
                                    'Estado' => $project->status?->value,
                                    'Cliente' => optional($project->customer)->name,
                                    'Fecha' => $project->created_at,
                                ];
                            }); }
                            public function headings(): array { return ['Código', 'Nombre', 'Fecha de inicio', 'Estado', 'Cliente', 'Fecha']; }
                        }, 'proyectos.xlsx');
                    }),
            ]);
    }
}
