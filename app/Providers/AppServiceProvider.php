<?php

namespace App\Providers;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();

        FilamentAsset::register([
            Css::make('glightbox-style', asset('vendor/glightbox/css/glightbox.min.css')),
            Js::make('glightbox-script', asset('vendor/glightbox/js/glightbox.min.js')),
            Css::make('main', asset('css/main.css'))
        ], package: 'app');
    }
}
