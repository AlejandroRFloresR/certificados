<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    public const TYPES = ['asistio', 'dicto', 'aprobado'];

    protected $fillable = [
        'user_id', 'course_id', 'issued_date', 'certificate_code', 'snapshot_data', 'type',
        'survey_token', 'emailed_at', 'email_error',
    ];

    protected $casts = [
        'issued_date'   => 'date',
        'snapshot_data' => 'array',
        'emailed_at'    => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function surveyResponse()
    {
        return $this->hasOne(SurveyResponse::class);
    }

    /** Los que dictaron el curso no reciben la encuesta */
    public static function takesSurvey(string $type): bool
    {
        return $type !== 'dicto';
    }
}
