<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use App\Models\Certificate;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Services\CertificateIssuer;
use App\Services\CertificatePdf;
use App\Services\CertificateTypeConflict;


class CertificateController extends Controller
{
    /**
     * EMITIR certificado (solo crea si no existe; NO descarga).
     * Pensado para panel admin.
     */
    public function emit(Request $request)
    {
        $data = $request->validate([
            'user_id'   => ['required','exists:users,id'],
            'course_id' => ['required','exists:courses,id'],
            'type'      => ['required', Rule::in(\App\Models\Certificate::TYPES)],
        ]);

        $user   = User::findOrFail($data['user_id']);
        $course = Course::findOrFail($data['course_id']);

        // (opcional) Autorización: debe estar inscripto
        if (!$user->courses()->where('course_id', $course->id)->exists()) {
            return back()->with('error', 'El usuario no está inscripto en este curso.');
        }

        // Garantiza un único certificado por (user, course) y tipo coherente
        $certificate = $this->issueOrAbort($user, $course, $data['type']);

        return back()
            ->with('success', 'Certificado emitido correctamente.')
            ->with('cert_code', $certificate->certificate_code);
    }

    /**
     * GENERAR/DESCARGAR el PDF.
     * Si no existe, lo crea (primer emisión) y descarga.
     * Acepta $type por URL o ?type=..., o reutiliza el del existente.
     */
    public function generate(Course $course, User $user, ?string $type = null)
    {
        // 1) Autorización
        if (!$user->courses()->where('course_id', $course->id)->exists()) {
            abort(403, 'No estás inscrito en este curso');
        }

        // 2) Resolver tipo: parámetro, query o el del certificado existente
        $existing = Certificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($type === null) {
            $type = request()->input('type') ?: ($existing->type ?? null);
        }
        if (!in_array($type, \App\Models\Certificate::TYPES, true)) {
            abort(422, 'Tipo de certificado inválido. Use: asistio, dicto o aprobado.');
        }

        // 3) Crear o devolver el existente (y validar coherencia de tipo)
        $certificate = $this->issueOrAbort($user, $course, $type);

        // 4) Render y descarga
        return $this->renderPdf($certificate);
    }

    /**
     * DESCARGAR por código (no emite, solo busca y baja).
     */
    public function downloadByCode(string $code)
    {
        $cert = Certificate::with(['user','course'])
            ->where('certificate_code', $code)
            ->firstOrFail();

        return $this->renderPdf($cert);
    }

    /**
     * HOME con búsqueda por DNI (sin cambios funcionales)
     */
    public function lookup(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $certs = collect();

        // Los datos del titular y del curso salen de la foto guardada al emitir:
        // así se verifica aunque el usuario o el curso se hayan eliminado o modificado
        if ($q !== '') {
            // ¿Parece un ULID? (26 chars base32 sin I, L, O, U)
            if (preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/i', $q)) {
                $certs = Certificate::where('certificate_code', $q)->get();
            } else {
                // Buscar por DNI (tomamos solo dígitos): certificados del usuario actual
                // y los de un usuario ya eliminado que tenía ese DNI
                $dni = preg_replace('/\D+/', '', $q);
                if ($dni !== '') {
                    $userIds = User::where('dni', $dni)->pluck('id');
                    $certs = Certificate::where(fn ($w) => $w
                            ->whereIn('user_id', $userIds)
                            ->orWhere(fn ($o) => $o->whereNull('user_id')->where('snapshot_data->student->dni', $dni)))
                        ->orderByDesc('created_at')
                        ->get();
                }
            }
        }

        $found = $certs->isNotEmpty();
        $first = $found ? $this->holderOf($certs->first()) : null;

        return Inertia::render('Home', [
            'q'        => $q,
            'searched' => $q !== '',
            'found'    => $found,
            'holder'   => $first,
            'certificates' => $certs->map(fn (Certificate $c) => [
                'code'         => $c->certificate_code,
                'course'       => data_get($c->snapshot_data, 'course.title') ?? $c->course?->title ?? '—',
                'type'         => $c->type ?? data_get($c->snapshot_data, 'type'),
                'issued'       => $c->issued_date ? Carbon::parse($c->issued_date)->format('d/m/Y') : null,
                'download_url' => route('certificates.download', $c->certificate_code),
            ])->values(),
        ]);
    }

    /** Titular del certificado: usuario actual si existe, si no el de la foto de la emisión */
    private function holderOf(Certificate $c): array
    {
        return [
            'name' => $c->user?->name ?? data_get($c->snapshot_data, 'student.name'),
            'dni'  => $c->user?->dni ?? data_get($c->snapshot_data, 'student.dni'),
        ];
    }

    /* ===========================
       Helpers privados (DRY)
       =========================== */

    /**
     * Crea el certificado si no existe para (user, course).
     * Si existe, valida coherencia de tipo y lo devuelve.
     */
        /** Emite usando el servicio; un conflicto de tipo responde 409 como antes */
    private function issueOrAbort(User $user, Course $course, string $type): Certificate
    {
        try {
            return app(CertificateIssuer::class)->issue($user, $course, $type);
        } catch (CertificateTypeConflict $e) {
            abort(409, $e->getMessage());
        }
    }

    /** Descarga el PDF (se arma desde la foto guardada al emitir) */
    private function renderPdf(Certificate $certificate)
    {
        $pdf = app(CertificatePdf::class);

        return $pdf->make($certificate)->download($pdf->filename($certificate));
    }
}
