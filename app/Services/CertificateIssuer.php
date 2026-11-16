<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Carbon\Carbon;
use App\Jobs\SendCertificateEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CertificateIssuer
{
    /**
     * Crea el certificado si no existe para (user, course).
     * Si existe y es del mismo tipo, lo devuelve.
     *
     * Un certificado nuevo se envía por correo a su titular ($notify), con la encuesta.
     *
     * @throws CertificateTypeConflict si ya existe uno de otro tipo
     */
    public function issue(User $user, Course $course, string $type, bool $notify = true): Certificate
    {
        $existing = Certificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            if (($existing->type ?? null) !== $type) {
                throw new CertificateTypeConflict('Ya existe un certificado de otro tipo para este curso y usuario.');
            }
            return $existing;
        }

        $courseDate = (string) ($course->start_date);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $courseDate)) {
            $ds = Carbon::createFromFormat('Y-m-d', $courseDate);
        } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $courseDate)) {
            $ds = Carbon::createFromFormat('d/m/Y', $courseDate);
        } else {
            $ds = Carbon::parse($courseDate);
        }
        $ds->locale('es');
        $start_date = $ds->translatedFormat('d \de\ F \de\ Y');

        // Snapshot inmutable de la emisión
        $snapshot = [
            'student' => [
                'name' => $user->name,
                'dni'  => $user->dni ?? null,
            ],
            'course' => [
                'title'       => $course->title,
                'description' => $course->description,
                'hours'       => $course->hours,
                'start_date'  => $start_date,
            ],
            // Firmantes del curso al momento de emitir (ruta de la firma incluida):
            // si después cambian, este certificado sigue mostrando los de hoy
            'signers'     => $course->signers()->get()->map(fn ($s) => [
                'name'      => $s->name,
                'position'  => $s->position,
                'signature' => $s->signature,
            ])->values()->all(),
            'issued_date' => now()->toDateString(),
            'type'        => $type,
            'assets'      => [
                'logo_rel_path' => 'images/logoCertificado.png',
            ],
            'qr' => [
                'size'   => 220,
                'margin' => 4,
                'color'  => [0, 0, 0],
                'bg'     => [255, 255, 255],
            ],
        ];

        $certificate = Certificate::create([
            'user_id'          => $user->id,
            'course_id'        => $course->id,
            'issued_date'      => now()->toDateString(),
            'certificate_code' => Str::ulid(),
            'survey_token'     => Certificate::takesSurvey($type) ? Str::random(40) : null,
            'snapshot_data'    => $snapshot,
            'type'             => $type,
        ]);

        // Recién cuando la emisión quedó guardada (si se está dentro de una transacción)
        if ($notify) {
            DB::afterCommit(fn () => SendCertificateEmail::dispatch($certificate->id));
        }

        return $certificate;
    }
}
