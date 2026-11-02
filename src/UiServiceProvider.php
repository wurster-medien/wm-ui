<?php

namespace WursterMedien\Ui;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Bindet das gemeinsame Design ein: Views als zusätzlicher Ort hinter resources/views der Anwendung (gleicher Name in der
 * Anwendung gewinnt), CSS und JS als Filament-Assets (veröffentlicht über php artisan filament:assets, das beim
 * composer install läuft), Grundeinstellungen der Komponenten über Ui::configure().
 */
class UiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::addLocation(__DIR__.'/../resources/views');

        FilamentAsset::register([
            Css::make('wm-ui', __DIR__.'/../resources/dist/wm-ui.css'),
            Js::make('wm-ui', __DIR__.'/../resources/dist/wm-ui.js'),
        ], 'wurster-medien/wm-ui');

        Ui::configure();
    }
}
