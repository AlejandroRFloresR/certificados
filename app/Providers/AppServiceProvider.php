<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
{
    $this->configureRootUrl();

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

    /**
     * Por el proxy (dominio) las URLs se arman con APP_URL, porque el proxy publica
     * el sistema en la raíz del dominio. Entrando directo por la IP interna se usa la
     * dirección real del pedido. Así funcionan las dos formas de acceso.
     */
    private function configureRootUrl(): void
    {
        $appUrl = config('app.url');

        if (!$appUrl) {
            return;
        }

        // Artisan, colas, etc.: no hay pedido HTTP, se usa APP_URL
        if ($this->app->runningInConsole()) {
            URL::forceRootUrl($appUrl);
            return;
        }

        $viaProxy = request()->server('REMOTE_ADDR') === config('app.proxy_ip');

        if ($viaProxy) {
            URL::forceRootUrl($appUrl);
            URL::forceScheme(parse_url($appUrl, PHP_URL_SCHEME) ?: 'https');
        }
    }
}