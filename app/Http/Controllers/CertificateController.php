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
        $certificate = $this->firstOrCreateCertificate($user, $course, $data['type']);

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
        $certificate = $this->firstOrCreateCertificate($user, $course, $type);

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
        $dni = trim((string) $request->query('dni', ''));
        $user = null;
        $certs = collect();

        if ($dni !== '') {
            $user = User::where('dni', $dni)->first();

            if ($user) {
                $certs = $user->certificates()
                    ->with('course:id,title')
                    ->orderByDesc('created_at')
                    ->get(['id','user_id','course_id','certificate_code','issued_date','type']);
            }
        }

        return view('home', compact('dni','user','certs'));
    }

    /* ===========================
       Helpers privados (DRY)
       =========================== */

    /**
     * Crea el certificado si no existe para (user, course).
     * Si existe, valida coherencia de tipo y lo devuelve.
     */
    private function firstOrCreateCertificate(User $user, Course $course, string $type): Certificate
    {
        $existing = Certificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            if (($existing->type ?? null) !== $type) {
                abort(409, 'Ya existe un certificado de otro tipo para este curso y usuario.');
            }
            return $existing;
        }

        // Snapshot inmutable de la emisión
        $course->load('tutors');
        $tutores = $course->tutors->map(fn($t) => [
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

    /**
     * Renderiza el PDF desde el snapshot del certificado y lo descarga.
     */
    private function renderPdf(Certificate $certificate)
    {
        // Asegurar relaciones
        $certificate->loadMissing(['user','course']);

        $snap = $certificate->snapshot_data;

        /* Logo (data URI) desde storage/public/images o fallback public/images */
        $logoData = null;
        $logoStorage = storage_path('app/public/images/logoCertificado.png');
        if (is_file($logoStorage)) {
            $logoData = 'data:image/png;base64,' . base64_encode(@file_get_contents($logoStorage));
        } else {
            $logoPublic = public_path('images/logoCertificado.png');
            if (is_file($logoPublic)) {
                $logoData = 'data:image/png;base64,' . base64_encode(@file_get_contents($logoPublic));
            }
        }

        /* Firmas (data URI) desde storage/app/public/signatures */
        $tutorsForView = collect($snap['tutors'] ?? [])->map(function ($t) {
            $file = basename((string)$t['signature']);
            $abs  = storage_path('app/public/signatures/' . $file);
            $dataUri = null;
            if (is_file($abs)) {
                $ext  = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
                $mime = ($ext === 'jpg' || $ext === 'jpeg') ? 'image/jpeg'
                      : ($ext === 'gif' ? 'image/gif' : 'image/png');
                $dataUri = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($abs));
            }
            return [
                'name'               => $t['name'],
                'signature_data_uri' => $dataUri,
            ];
        })->all();

        /* Fecha corta y larga */
        $rawDate = (string)($snap['issued_date'] ?? $certificate->issued_date);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate)) {
            $date = Carbon::createFromFormat('Y-m-d', $rawDate)->format('d/m/Y');
            $dt   = Carbon::createFromFormat('Y-m-d', $rawDate);
        } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $rawDate)) {
            $date = $rawDate;
            $dt   = Carbon::createFromFormat('d/m/Y', $rawDate);
        } else {
            $date = Carbon::parse($rawDate)->format('d/m/Y');
            $dt   = Carbon::parse($rawDate);
        }
        $dt->locale('es');
        $dateLong = $dt->translatedFormat('d \de\ F \de\ Y');

        /* QR (SVG data URI) hacia la verificación */
        $verifyUrl = route('certificates.verify', $certificate->certificate_code);
        [$r,$g,$b] = [0,55,100]; // #003764
        $qrHeaderSvg = QrCode::format('svg')
            ->size(80)
            ->margin(2)
            ->color(255,255,255)        // módulos blancos
            ->backgroundColor($r,$g,$b) // fondo azul
            ->generate($verifyUrl);
        $qrHeaderDataUri = 'data:image/svg+xml;base64,' . base64_encode($qrHeaderSvg);

        /* Marca de agua (desde storage/app/public/images/watermark.jpeg) */
        $wmRel = $snap['assets']['watermark_storage_rel'] ?? 'images/watermark.jpeg';
        $wmAbs = storage_path('app/public/' . $wmRel);
        $watermarkData = null;
        if (is_file($wmAbs)) {
            $ext  = strtolower(pathinfo($wmAbs, PATHINFO_EXTENSION));
            $mime = ($ext === 'jpg' || $ext === 'jpeg') ? 'image/jpeg' : 'image/png';
            $watermarkData = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($wmAbs));
        }

        /* Datos para la vista PDF */
        $data = [
            'snap'           => $snap,
            'logo'           => null,
            'logo_data'      => $logoData,
            'tutors'         => $tutorsForView,
            'code'           => $certificate->certificate_code,
            'qr_data_uri'    => $qrHeaderDataUri,
            'watermark_data' => $watermarkData,
            'date'           => $date,
            'date_long'      => $dateLong,
        ];

        $pdf = Pdf::loadView('certificates.course', $data)
            ->setPaper('A4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
            ]);

        $user   = $certificate->user;
        $course = $certificate->course;

        return $pdf->download("Certificado_{$user->name}_{$course->title}.pdf");
    }
}
