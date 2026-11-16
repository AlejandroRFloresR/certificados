<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Course extends Model
{
    use HasFactory;

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /** Firmantes del certificado, en el orden en que se imprimen */
    public function signers()
    {
        return $this->belongsToMany(Signer::class)->withPivot('sort_order')->orderByPivot('sort_order');
    }

    protected $fillable = [
        // 'hours' ya no se edita: la columna queda solo para conservar lo cargado antes
        'title', 'description', 'start_date', 'end_date',
        'is_public', 'category_id', 'modality', 'location', 'moodle_shortname',
    ];
    protected $casts =[
        'hours'      => 'integer',
        'is_public'  => 'boolean',
    ];

    public const MODALITIES = [
        'presencial' => 'Presencial',
        'virtual'    => 'Virtual',
        'hibrido'    => 'Híbrido',
    ];

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /** proximo | en_curso | finalizado | sin_fecha */
    public function getStatusAttribute(): string
    {
        $today = now()->startOfDay();
        $start = $this->start_date ? Carbon::parse($this->start_date) : null;
        $end   = $this->end_date ? Carbon::parse($this->end_date) : null;

        if (!$start) {
            return 'sin_fecha';
        }
        if ($start->gt($today)) {
            return 'proximo';
        }
        if (!$end || $end->gte($today)) {
            return 'en_curso';
        }
        return 'finalizado';
    }

    /** Datos de la cabecera de la ficha del curso (pestañas Alumnos y Moodle) */
    public function adminSummary(): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'category'         => $this->category?->name,
            'modality'         => self::MODALITIES[$this->modality] ?? null,
            'start_date'       => $this->start_date,
            'end_date'         => $this->end_date,
            'status'           => $this->status,
            'is_public'        => $this->is_public,
            'moodle_shortname' => $this->moodle_shortname,
            'students'         => $this->users()->count(),
        ];
    }
}