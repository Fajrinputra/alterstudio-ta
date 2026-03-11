<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

/**
 * Konfigurasi global aplikasi pada saat bootstrap.
 */
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
        // Locale Carbon untuk format tanggal/hari pada tampilan.
        Carbon::setLocale(config('app.locale'));

        // Best effort untuk locale tanggal level sistem.
        setlocale(LC_TIME, 'id_ID.UTF-8', 'id_ID', 'Indonesian_Indonesia.1252', 'Indonesian');
    }
}
