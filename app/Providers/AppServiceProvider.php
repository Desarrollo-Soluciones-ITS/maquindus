<?php

namespace App\Providers;

use App\Services\SearchIndexer;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SearchIndexer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();

        Table::configureUsing(function (Table $table) {
            $table->filtersLayout(FiltersLayout::Modal)
                ->defaultPaginationPageOption(5)
                ->filtersTriggerAction(function (Action $action) {
                    $action->button()
                        ->label('Filtros');
                });
        });

        TextColumn::configureUsing(function (TextColumn $column) {
            $column->placeholder('N/A');
        });

        TextEntry::configureUsing(function (TextEntry $entry) {
            $entry->placeholder('N/A');
        });

        RepeatableEntry::configureUsing(function (RepeatableEntry $entry) {
            $entry->placeholder('N/A');
        });

        Select::configureUsing(function (Select $select) {
            $select->preload();
        });

        SelectFilter::configureUsing(function (SelectFilter $select) {
            $select->preload();
        });

        MorphToSelect::configureUsing(function (MorphToSelect $select) {
            $select->preload();
        });

        AttachAction::configureUsing(function (AttachAction $attach) {
            $attach->preloadRecordSelect();
        });

        FilamentAsset::register([
            Css::make('glightbox-style', asset('vendor/glightbox/css/glightbox.min.css')),
            Js::make('glightbox-script', asset('vendor/glightbox/js/glightbox.min.js')),
            Css::make('main', asset('css/main.css'))
        ], package: 'app');

        Password::defaults(function () {
            return Password::min(8)
                ->mixedCase()
                ->symbols()
                ->letters()
                ->numbers()
                ->max(20);
        });
    }
}
