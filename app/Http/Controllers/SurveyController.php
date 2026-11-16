<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Encuesta de satisfacción: se responde con el link único del correo (sin iniciar sesión),
 * una vez por certificado. El admin ve los resultados por curso, sin nombres.
 */
class SurveyController extends Controller
{
    public function show(string $token): Response
    {
        $certificate = $this->certificate($token);
        $snap = $certificate->snapshot_data ?? [];

        return Inertia::render('Survey', [
            'token'     => $token,
            'course'    => data_get($snap, 'course.title') ?? $certificate->course?->title,
            'firstName' => strtok((string) (data_get($snap, 'student.name') ?? ''), ' ') ?: null,
            'answered'  => $certificate->surveyResponse()->exists(),
            'downloadUrl' => route('certificates.download', $certificate->certificate_code),
            'questions' => SurveyResponse::questions(),
        ]);
    }

    public function store(Request $request, string $token)
    {
        $certificate = $this->certificate($token);

        if ($certificate->surveyResponse()->exists()) {
            return redirect()->route('survey.show', $token);
        }

        $rating = ['required', 'integer', 'between:1,5'];
        $data = $request->validate(
            array_fill_keys(array_keys(SurveyResponse::RATINGS), $rating) + [
                'recommend' => ['required', 'boolean'],
                'comment'   => ['nullable', 'string', 'max:2000'],
            ],
            ['required' => 'Elegí una opción.', 'comment.max' => 'El comentario puede tener hasta 2000 caracteres.'],
        );

        SurveyResponse::firstOrCreate(
            ['certificate_id' => $certificate->id],
            $data + ['course_id' => $certificate->course_id],
        );

        return redirect()->route('survey.show', $token);
    }

    /** Resultados del curso (admin) */
    public function results(Course $course): Response
    {
        $responses = SurveyResponse::where('course_id', $course->id)->latest()->get();
        $total = $responses->count();

        $ratings = collect(SurveyResponse::RATINGS)->map(fn ($text, $key) => [
            'key'          => $key,
            'text'         => $text,
            'average'      => $total ? round($responses->avg($key), 1) : null,
            // Cantidad de respuestas por puntaje, de 5 a 1
            'distribution' => collect([5, 4, 3, 2, 1])->map(fn ($v) => [
                'value' => $v,
                'label' => SurveyResponse::SCALE[$v],
                'count' => $responses->where($key, $v)->count(),
            ])->values(),
        ])->values();

        return Inertia::render('Admin/Courses/Survey', [
            'course'  => $course->adminSummary(),
            'invited' => $course->certificates()->whereNotNull('survey_token')->count(),
            'total'   => $total,
            'ratings' => $ratings,
            'recommend' => [
                'text' => SurveyResponse::RECOMMEND,
                'yes'  => $responses->where('recommend', true)->count(),
            ],
            'comments' => $responses->filter(fn ($r) => filled($r->comment))->map(fn ($r) => [
                'id'      => $r->id,
                'comment' => $r->comment,
                'overall' => $r->overall,
                'date'    => $r->created_at->format('d/m/Y'),
            ])->values(),
        ]);
    }

    private function certificate(string $token): Certificate
    {
        return Certificate::where('survey_token', $token)->firstOrFail();
    }
}
