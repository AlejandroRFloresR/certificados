<?php

namespace App\Mail;

use App\Models\Certificate;
use App\Services\CertificatePdf;
use App\Support\PublicUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Correo de emisión: certificado en PDF adjunto + invitación a la encuesta.
 * Los datos salen de la foto guardada al emitir (lo mismo que dice el PDF).
 */
class CertificateIssuedMail extends Mailable
{
    use Queueable;

    public function __construct(public Certificate $certificate)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Tu certificado: ' . $this->courseTitle());
    }

    public function content(): Content
    {
        $c = $this->certificate;

        return new Content(markdown: 'emails.certificate-issued', with: [
            'name'        => data_get($c->snapshot_data, 'student.name') ?? $c->user?->name,
            'courseTitle' => $this->courseTitle(),
            'taught'      => $c->type === 'dicto',
            'code'        => $c->certificate_code,
            'downloadUrl' => PublicUrl::route('certificates.download', $c->certificate_code),
            'verifyUrl'   => PublicUrl::route('home', ['q' => $c->certificate_code]),
            'surveyUrl'   => $c->survey_token ? PublicUrl::route('survey.show', $c->survey_token) : null,
        ]);
    }

    public function attachments(): array
    {
        $pdf = app(CertificatePdf::class);

        return [
            Attachment::fromData(fn () => $pdf->make($this->certificate)->output(), $pdf->filename($this->certificate))
                ->withMime('application/pdf'),
        ];
    }

    private function courseTitle(): string
    {
        return data_get($this->certificate->snapshot_data, 'course.title') ?? $this->certificate->course?->title ?? 'Curso';
    }
}
