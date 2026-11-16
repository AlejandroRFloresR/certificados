<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    /** Preguntas de puntaje (1 a 5): columna => texto */
    public const RATINGS = [
        'overall'      => 'En general, ¿cómo calificarías el curso?',
        'content'      => '¿Qué tan útiles fueron los contenidos para tu trabajo?',
        'teachers'     => '¿Cómo calificarías a los docentes?',
        'organization' => '¿Cómo calificarías la organización (horarios, materiales, plataforma)?',
    ];

    public const SCALE = [1 => 'Muy malo', 2 => 'Malo', 3 => 'Regular', 4 => 'Bueno', 5 => 'Excelente'];

    public const RECOMMEND = '¿Recomendarías este curso a un colega?';
    public const COMMENT   = '¿Qué te gustó o qué mejorarías?';

    protected $fillable = ['certificate_id', 'course_id', 'overall', 'content', 'teachers', 'organization', 'recommend', 'comment'];

    protected $casts = ['recommend' => 'boolean'];

    public function certificate()
    {
        return $this->belongsTo(Certificate::class);
    }

    /** Preguntas para el formulario (React) */
    public static function questions(): array
    {
        return [
            'ratings'   => collect(self::RATINGS)->map(fn ($text, $key) => ['key' => $key, 'text' => $text])->values(),
            'scale'     => collect(self::SCALE)->map(fn ($label, $value) => ['value' => $value, 'label' => $label])->values(),
            'recommend' => self::RECOMMEND,
            'comment'   => self::COMMENT,
        ];
    }
}
