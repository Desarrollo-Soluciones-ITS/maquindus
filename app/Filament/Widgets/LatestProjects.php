<?php

namespace App\Filament\Widgets;

use App\Enums\Status;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Models\Project;
use Filament\Actions\BulkActionGroup;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class LatestProjects extends TableWidget
{
    protected static ?string $heading = 'Últimos proyectos';

    protected function getCachedProjects()
    {
        return Cache::remember('latest_projects_widget', 120, function () {
            return Project::latest()
                ->limit(5)
                ->get(['id', 'code', 'name', 'status', 'customer_id', 'created_at']);
        });
    }

    public function table(Table $table): Table
    {
        $projects = $this->getCachedProjects();

        return $table
            ->query(
                fn(): Builder => Project::with(['customer'])
                    ->whereIn('id', $projects->pluck('id'))
            )
            ->columns([
                TextColumn::make('code')
                    ->label('Código'),
                TextColumn::make('name')
                    ->label('Nombre'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn($state): string => match ($state) {
                        Status::Planning => 'primary',
                        Status::Ongoing => 'warning',
                        Status::Finished => 'success',
                    }),
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->color(Color::Blue)
                    ->url(
                        fn($record) =>
                        ViewCustomer::getUrl(['record' => $record->customer_id])
                    ),
            ])
            ->paginated(false)
            ->recordUrl(
                fn(Project $record): string => route('filament.dashboard.resources.projects.view', ['record' => $record]),
            )
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
