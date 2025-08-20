<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use App\Models\Certificate;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

class CertificateController extends Controller
{
    public function generate(Course $course, User $user)
    {
        // 1) Autorización
        if (!$user->courses()->where('course_id', $course->id)->exists()) {
            abort(403, 'No estás inscrito en este curso');
        }

        // 2) Buscar certificado existente
        $certificate = Certificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (!$certificate) {
            // 3) Primera emisión → snapshot inmutable
            $course->load('tutors');

            $tutores = $course->tutors->map(fn($t) => [
                'name'      => $t->name,
                'signature' => $t->signature, // archivo en storage/app/public/signatures
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
                'issued_date' => now()->toDateString(), // YYYY-MM-DD
                'assets'      => [
                    'logo_rel_path' => 'images/logoCertificado.png',
                    // opcional: si querés persistir la ruta de watermark en snapshot:
                    // 'watermark_storage_rel' => 'images/watermark.jpeg',
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
                'snapshot_data'    => $snapshot,
            ]);
        }

        // 4) Snapshot
        $snap = $certificate->snapshot_data;

         // 5) Logo absoluto (en public/)
        $logoAbs = isset($snap['assets']['logo_rel_path'])
            ? public_path($snap['assets']['logo_rel_path'])
            : null;

        // 5.1) LOGO como data-URI (INTENTA primero en storage/, luego en public/)
        $logoData = null;
        $candidatosLogo = [
            storage_path('app/public/images/logoCertificado.png'),                // <-- storage
            isset($snap['assets']['logo_rel_path']) ? public_path($snap['assets']['logo_rel_path']) : null, // fallback public
            public_path('images/logoCertificado.png'),                            // fallback directo
        ];
        foreach ($candidatosLogo as $p) {
            if ($p && is_file($p)) {
                $ext  = strtolower(pathinfo($p, PATHINFO_EXTENSION));
                $mime = ($ext === 'jpg' || $ext === 'jpeg') ? 'image/jpeg'
                      : ($ext === 'gif' ? 'image/gif' : 'image/png');
                $logoData = 'data:' . $mime . ';base64,' . base64_encode(@file_get_contents($p));
                break;
            }
        }

        // 6) Firmas → data-URI (desde storage/app/public/signatures)
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

        // 7) Fecha d/m/Y (robusto)
        $rawDate = (string)($snap['issued_date'] ?? $certificate->issued_date);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate)) {
            $date = Carbon::createFromFormat('Y-m-d', $rawDate)->format('d/m/Y');
        } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $rawDate)) {
            $date = $rawDate;
        } else {
            $date = Carbon::parse($rawDate)->format('d/m/Y');
        }

        // 8) QR → data-URI (SVG)
        $verifyUrl = route('certificates.verify', $certificate->certificate_code);
        [$r,$g,$b] = [0,55,100]; // #003764
        $qrHeaderSvg = QrCode::format('svg')
            ->size(80)                 // tamaño pequeño para el header
            ->margin(2)
            ->color(255,255,255)       // módulos BLANCOS
            ->backgroundColor($r,$g,$b) // fondo azul igual al header
            ->generate($verifyUrl);
        $qrHeaderDataUri = 'data:image/svg+xml;base64,' . base64_encode($qrHeaderSvg);

        // 9) Marca de agua → data-URI DESDE storage/app/public/images/watermark.jpeg
        //    (No usa public/, lee del storage real)
        $wmRel = $snap['assets']['watermark_storage_rel'] ?? 'images/watermark.jpeg';
        $wmAbs = storage_path('app/public/' . $wmRel); // <-- TU RUTA
        $watermarkData = null;
        if (is_file($wmAbs)) {
            $ext  = strtolower(pathinfo($wmAbs, PATHINFO_EXTENSION));
            $mime = ($ext === 'jpg' || $ext === 'jpeg') ? 'image/jpeg' : 'image/png';
            $watermarkData = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($wmAbs));
        }

        // 10) Datos a la vista
        $data = [
            'snap'           => $snap,
            'logo'           => $logoAbs,
            'tutors'         => $tutorsForView,
            'code'           => $certificate->certificate_code,
            'qr_data_uri'    => $qrHeaderDataUri,
            'watermark_data' => $watermarkData,   // <- watermark desde storage
            'date'           => $date,
        ];

        // 11) Render PDF
        $pdf = Pdf::loadView('certificates.course', $data)
            ->setPaper('A4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
            ]);

        return $pdf->download("Certificado_{$user->name}_{$course->title}.pdf");
    }
}
