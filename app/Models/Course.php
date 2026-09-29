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
    
    public function tutors()
    {
        return $this->belongsToMany(Tutor::class);
    }
    
    protected $fillable = [
        'title', 'description', 'start_date', 'end_date', 'hours',
        'is_public', 'category', 'modality', 'location',
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
}