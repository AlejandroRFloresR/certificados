<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CertificateIssuer
{
    /**
     * Crea el certificado si no existe para (user, course).
     * Si existe y es del mismo tipo, lo devuelve.
     *
     * @throws CertificateTypeConflict si ya existe uno de otro tipo
     */
    public function issue(User $user, Course $course, string $type): Certificate
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
        $course->loadMissing('tutors');
        $tutores = $course->tutors->map(fn ($t) => [
            'name'      => $t->name,
            'signature' => $t->signature,
        ])->values()->all();

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
            'tutors'      => $tutores,
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

        return Certificate::create([
            'user_id'          => $user->id,
            'course_id'        => $course->id,
            'issued_date'      => now()->toDateString(),
            'certificate_code' => Str::ulid(),
            'snapshot_data'    => $snapshot,
            'type'             => $type,
        ]);
    }
}
