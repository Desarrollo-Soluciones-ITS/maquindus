<?php

namespace App\Filament\Widgets;

use App\Enums\Category;
use App\Filament\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Filament\Resources\Equipment\Pages\ViewEquipment;
use App\Filament\Resources\Parts\Pages\ViewPart;
use App\Filament\Resources\People\Pages\ViewPerson;
use App\Filament\Resources\Projects\Pages\ViewProject;
use App\Filament\Resources\Suppliers\Pages\ViewSupplier;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Equipment;
use App\Models\Part;
use App\Models\Person;
use App\Models\Project;
use App\Models\Supplier;
use Filament\Actions\BulkActionGroup;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class LatestDocuments extends TableWidget
{
    protected static ?string $heading = 'Últimos documentos';

    protected function getCachedDocuments()
    {
        return Cache::remember('latest_documents_widget', 120, function () {
            return Document::with('documentable')
                ->latest()
                ->limit(5)
                ->get(['id', 'name', 'category']);
        });
    }

    public function table(Table $table): Table
    {
        $documents = $this->getCachedDocuments();

        return $table
            ->query(
                fn(): Builder => Document::whereIn('id', $documents->pluck('id'))
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre'),
                TextColumn::make('current.mime')
                    ->label('Tipo de archivo')
                    ->badge(),
                TextColumn::make('category')
                    ->label('Categoría')
                    ->badge()
                    ->color(fn($state): string => Category::colors($state)),
                TextColumn::make('documentable.name')
                    ->label('Pertenece a')
                    ->formatStateUsing(function (Model $record, $state) {
                        $model = $record->documentable_type;
                        $spanish = model_to_spanish($model) ?? 'Relacionado';
                        return "($spanish) $state";
                    })
                    ->color(Color::Blue)
                    ->url(function (Model $record) {
                        $class = $record->documentable::class;

                        $page = match ($class) {
                            Part::class => ViewPart::class,
                            Person::class => ViewPerson::class,
                            Project::class => ViewProject::class,
                            Supplier::class => ViewSupplier::class,
                            Customer::class => ViewCustomer::class,
                            Equipment::class => ViewEquipment::class,
                        };

                        return $page::getUrl([
                            'record' => $record->documentable->id
                        ]);
                    }),
                TextColumn::make('current.created_at')
                    ->label('Última versión')
                    ->date('d/m/Y - g:i A')
                    ->timezone('America/Caracas')
            ])
            ->paginated(false)
            ->recordUrl(
                fn(Document $record): string => route('filament.dashboard.resources.documents.view', ['record' => $record]),
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
