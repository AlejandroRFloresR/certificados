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

        // 5) LOGO: buscar en storage/app/public/images y fallback a public/images (png/jpg/jpeg)
        $logoData = null;
        $logoStorage = storage_path('app/public/images/logoCertificado.png');
        if (is_file($logoStorage)) {
            // si en el futuro usas .jpg/.jpeg, ajusta el mime
            $logoData = 'data:image/png;base64,' . base64_encode(@file_get_contents($logoStorage));
        } else {
            // fallback opcional: public/images/logoCertificado.png
            $logoPublic = public_path('images/logoCertificado.png');
            if (is_file($logoPublic)) {
                $logoData = 'data:image/png;base64,' . base64_encode(@file_get_contents($logoPublic));
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
        // 7.1) Fecha larga en español: "01 de julio de 2025"
        $dt = null;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate)) {
            $dt = Carbon::createFromFormat('Y-m-d', $rawDate);
        } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $rawDate)) {
            $dt = Carbon::createFromFormat('d/m/Y', $rawDate);
        } else {
            $dt = Carbon::parse($rawDate);
        }
        $dt->locale('es');
        $dateLong = $dt->translatedFormat('d \de\ F \de\ Y'); 

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
            'logo'           => null,
            'logo_data'      => $logoData,
            'tutors'         => $tutorsForView,
            'code'           => $certificate->certificate_code,
            'qr_data_uri'    => $qrHeaderDataUri,
            'watermark_data' => $watermarkData,   // <- watermark desde storage
            'date'           => $date,
            'date_long'      => $dateLong,
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
        public function lookup(Request $request)
    {
        $dni = trim((string) $request->query('dni', ''));
        $user = null;
        $certs = collect();

        if ($dni !== '') {
            // Buscamos el usuario por DNI exacto (tu User ya lo tiene unique)
            $user = \App\Models\User::where('dni', $dni)->first();

            if ($user) {
                // Traemos certificados + curso (ordenados por fecha de emisión desc si existe)
                $certs = $user->certificates()
                    ->with('course:id,title')
                    ->orderByDesc('created_at') // si tu columna se llama distinto, ajustalo
                    ->get(['id','user_id','course_id','certificate_code','issued_date']);
            }
        }

        // Vista pública con el formulario y, si hay DNI, mostramos resultados
        return view('home', compact('dni','user','certs'));
    }
}
