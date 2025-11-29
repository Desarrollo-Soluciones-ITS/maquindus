<?php

namespace App\Filament\Pages;

use App\Services\SearchIndexer;
use App\Traits\Searchable;
use Carbon\Carbon;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Symfony\Component\Finder\Finder;
use BackedEnum;

class GlobalSearchPage extends Page implements HasForms
{
    use InteractsWithForms;
    use \Livewire\WithPagination;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::MagnifyingGlass;

    protected static ?string $title = 'Buscador';

    protected string $view = 'filament.pages.global-search-page';

    #[Url]
    public ?string $query = '';

    #[Url(history: true)]
    public ?array $selectedModels = [];

    public array $searchResults = [];

    public bool $isLoading = false;

    public int $perPage = 10;
    public int $totalResults = 0;

    public function mount(): void
    {
        $this->form->fill();
        $this->selectedModels = $this->selectedModels ?? [];
        $this->performSearch();
    }

    public function updatedPage(): void
    {
        $this->performSearch();
    }

    public function updatedQuery(): void
    {
        $this->resetPage();
        $this->performSearch();
    }

    public function updatedSelectedModels(): void
    {
        $this->resetPage();
        $this->performSearch();
    }

    public function toggleModel(string $model): void
    {
        if (in_array($model, $this->selectedModels)) {
            $this->selectedModels = array_diff($this->selectedModels, [$model]);
        } else {
            $this->selectedModels[] = $model;
        }
        $this->selectedModels = array_values($this->selectedModels);
        $this->resetPage();
        $this->performSearch();
    }

    public function clearFilters(): void
    {
        $this->selectedModels = [];
        $this->query = '';
        $this->resetPage();
        $this->searchResults = [];
        $this->performSearch();
    }

    public function getPaginatorProperty()
    {
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $this->searchResults,
            $this->totalResults,
            $this->perPage,
            $this->getPage(),
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    public function performSearch(): void
    {
        $this->isLoading = true;

        try {
            $result = app(SearchIndexer::class)->search(
                query: $this->query ?? '',
                models: $this->selectedModels,
                perPage: $this->perPage,
                page: $this->getPage()
            );

            $this->searchResults = array_map(function ($item) {
                return [
                    'uuid' => $item->model_id,
                    'model_type' => $item->model_type,
                    'name' => $item->result_name,
                    'description' => $item->result_description,
                    'created_at' => $this->formatDateForDisplay($item->created_at),
                    'updated_at' => $this->formatDateForDisplay($item->updated_at),
                ];
            }, $result['data']);

            $this->totalResults = $result['total'];

        } catch (\Exception $e) {
            Log::error('Error al buscar: ' . $e->getMessage());
            $this->searchResults = [];
            $this->totalResults = 0;
        } finally {
            $this->isLoading = false;
        }
    }

    protected function getResourceUrl(string $modelType, string $uuid): string
    {
        $resource = $this->modelToResource($modelType);
        return "/{$resource}/{$uuid}";
    }

    protected function formatDateForDisplay($date): string
    {
        if (!$date) {
            return '–';
        }

        if ($date instanceof Carbon) {
            return $date->setTimezone('America/Caracas')->format('d/m/Y g:i A');
        }

        if (is_string($date)) {
            try {
                return Carbon::parse($date)->setTimezone('America/Caracas')->format('d/m/Y g:i A');
            } catch (\Exception $e) {
                if (is_numeric($date)) {
                    try {
                        return Carbon::createFromTimestamp($date)->setTimezone('America/Caracas')->format('d/m/Y g:i A');
                    } catch (\Exception $e) {
                        return '–';
                    }
                }
                return '–';
            }
        }

        return '–';
    }

    protected function getFormSchema(): array
    {
        $modelOptions = [];
        foreach ($this->getSearchableModels() as $model) {
            if (class_exists($model)) {
                $modelOptions[$model] = $this->modelToSpanish($model);
            }
        }

        return [
            TextInput::make('query')
                ->label(false)
                ->type('search')
                ->placeholder('Buscar en todo el sistema...')
                ->live(debounce: 300)
                ->columnSpanFull()
                ->autofocus()
                ->hiddenLabel()
                ->dehydrateStateUsing(fn($state) => trim($state))
                ->extraInputAttributes(['class' => 'text-xl py-3'])
                ->prefixIcon(Heroicon::MagnifyingGlass),

            CheckboxList::make('selectedModels')
                ->label('Tipos')
                ->hiddenLabel()
                ->options($modelOptions)
                ->columns(4)
                ->gridDirection('row')
                ->live()
                ->visible(!empty($modelOptions))
                ->dehydrated(false),
        ];
    }

    protected function getSearchableModels(): array
    {
        $models = [];
        $finder = new Finder();
        $finder->files()
            ->name('*.php')
            ->in(app_path('Models'))
            ->notName('Model.php');

        foreach ($finder as $file) {
            $relativePath = $file->getRelativePathname();
            $className = 'App\\Models\\' . Str::before($relativePath, '.php');
            $className = str_replace('/', '\\', $className);

            if (
                class_exists($className) &&
                is_subclass_of($className, Model::class) &&
                in_array(Searchable::class, class_uses_recursive($className))
            ) {
                $models[] = $className;
            }
        }

        return $models;
    }

    protected function modelToSpanish(string $modelType): string
    {
        $map = [
            'App\\Models\\Customer' => 'Cliente',
            'App\\Models\\Project' => 'Proyecto',
            'App\\Models\\Equipment' => 'Equipo',
            'App\\Models\\Part' => 'Parte',
            'App\\Models\\Supplier' => 'Proveedor',
            'App\\Models\\Person' => 'Persona',
            'App\\Models\\Activity' => 'Log de Actividad',
            'App\\Models\\User' => 'Usuario',
            'App\\Models\\Document' => 'Documento',
        ];

        return $map[$modelType] ?? class_basename($modelType);
    }

    protected function modelToResource(string $modelType): string
    {
        $map = [
            'App\\Models\\Customer' => 'customers',
            'App\\Models\\Project' => 'projects',
            'App\\Models\\Equipment' => 'equipment',
            'App\\Models\\Part' => 'parts',
            'App\\Models\\Supplier' => 'suppliers',
            'App\\Models\\Person' => 'people',
            'App\\Models\\Activity' => 'activity-logs',
            'App\\Models\\Document' => 'documents',
        ];

        return $map[$modelType] ?? Str::plural(Str::lower(class_basename($modelType)));
    }

    protected function highlightText(string $text, ?string $term): string
    {
        if (!$term || !$text) {
            return $text;
        }

        $escapedTerm = preg_quote($term, '/');
        $pattern = "/($escapedTerm)/i";

        return preg_replace($pattern, '<mark class="bg-yellow-50 text-gray-700 px-1 rounded underline decoration-yellow-300">$1</mark>', $text);
    }

    public static function canAccess(): bool
    {
        return currentUserHasPermission('search');
    }
}