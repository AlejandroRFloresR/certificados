<?php

namespace App\Services;

use App\Models\Certificate;
use App\Support\PublicUrl;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * PDF del certificado. Lo usan la descarga y el correo de emisión.
 */
class CertificatePdf
{
    /** Arma el PDF desde el snapshot del certificado */
    public function make(Certificate $certificate): \Barryvdh\DomPDF\PDF
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

        /* Firmas (data URI) desde storage/app/public/signatures.
           Certificados nuevos: $snap['signers'] (firmantes). Certificados de antes:
           $snap['tutors']. NO borrar este bloque ni la carpeta signatures. */
        $tutorsForView = collect($snap['signers'] ?? $snap['tutors'] ?? [])->map(function ($t) {
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
                'position'           => $t['position'] ?? null,
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
        $verifyUrl = PublicUrl::route('home', ['q' => $certificate->certificate_code]);    
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

        return Pdf::loadView('certificates.course', $data)
            ->setPaper('A4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
            ]);
    }

    public function filename(Certificate $certificate): string
    {
        $snap  = $certificate->snapshot_data ?? [];
        $name  = data_get($snap, 'student.name') ?? $certificate->user?->name ?? 'alumno';
        $title = data_get($snap, 'course.title') ?? $certificate->course?->title ?? 'curso';

        return "Certificado_{$name}_{$title}.pdf";
    }
}
