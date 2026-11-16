<?php

namespace App\Support;

/**
 * URLs para afuera del sistema (correos y QR de los certificados).
 * Si PUBLIC_URL está configurada (ej: https://dominio/sistemacertificados/public) se usa esa,
 * así un correo enviado mientras el admin entra por la IP interna no lleva links a esa IP.
 * Si no, se usa la dirección por la que se está accediendo (comportamiento anterior).
 */
class PublicUrl
{
    public static function route(string $name, mixed $parameters = []): string
    {
        $base = config('app.public_url');

        return $base
            ? rtrim($base, '/') . route($name, $parameters, false)
            : route($name, $parameters);
    }
}
