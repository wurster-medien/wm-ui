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
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/wm-ui.php', 'wm-ui');
    }

    public function boot(): void
    {
        View::addLocation(__DIR__.'/../resources/views');

        /** Mails: Bausteine und Theme aus dem Paket, resources/views/vendor/mail der Anwendung geht vor (Logo, Fuß). */
        View::prependNamespace('notifications', __DIR__.'/../resources/views/notifications');
        config([
            'mail.markdown.theme' => 'wurster',
            'mail.markdown.paths' => [resource_path('views/vendor/mail'), __DIR__.'/../resources/views/mail'],
        ]);

        FilamentAsset::register([
            Css::make('wm-ui', __DIR__.'/../resources/dist/wm-ui.css'),
            Js::make('wm-ui', __DIR__.'/../resources/dist/wm-ui.js'),
        ], 'wurster-medien/wm-ui');

        Ui::configure();
    }
}
