<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name'];

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    /**
     * Devuelve la categoría con ese nombre, creándola si no existe.
     * La búsqueda no distingue mayúsculas ni tildes (intercalación de MySQL),
     * así "enfermeria" encuentra "Enfermería" en vez de crear un duplicado.
     */
    public static function resolve(?string $name): ?self
    {
        $name = trim(preg_replace('/\s+/', ' ', (string) $name));

        return $name === '' ? null : static::firstOrCreate(['name' => $name]);
    }
}
