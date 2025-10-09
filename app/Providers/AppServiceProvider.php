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
    // Directorios y permisos de acceso de Dompdf
    config([
        // Dompdf tiene que poder "ver" storage/app y public
        'dompdf.options.chroot' => base_path(), // o public_path(), cualquiera que abarque /storage y /public
        'dompdf.font_dir'       => storage_path('app/fonts'),
        'dompdf.font_cache'     => storage_path('app/fonts/cache'),
        // Para que el fallback no sea Helvetica
        'dompdf.default_font'   => 'verdana',
        'dompdf.options.defaultFont'  => 'verdana',
        'dompdf.options.isRemoteEnabled' => true,
    ]);
}
}