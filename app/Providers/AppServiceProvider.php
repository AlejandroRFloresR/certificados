<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Ruta base donde guardaste las fuentes
        $base = storage_path('app/fonts/Montserrat');

        // Aseguramos opciones de DomPDF (barryvdh/laravel-dompdf) vía config()
        // Nota: estas claves existen en config/dompdf.php (no hace falta publicarlo si no querés).
        config([
            // Directorios de fuentes y cache
            'dompdf.font_dir'   => storage_path('app/fonts'),
            'dompdf.font_cache' => storage_path('app/fonts/cache'),

            // Habilitar recursos locales/remotos si hiciera falta
            'dompdf.options.isRemoteEnabled' => true,

            // Registrar familia "montserrat" con sus variantes
            'dompdf.fonts' => array_merge(
                config('dompdf.fonts', []),
                [
                    // El nombre de familia que usarás en CSS: font-family: montserrat;
                    'montserrat' => [
                        'R'  => $base . '/Montserrat-Regular.ttf',      // normal
                        'B'  => $base . '/Montserrat-Bold.ttf',         // bold
                        'I'  => $base . '/Montserrat-Italic.ttf',       // italic
                        'BI' => $base . '/Montserrat-BoldItalic.ttf',   // bold italic (opcional)
                    ],
                ]
            ),
        ]);
    }
}