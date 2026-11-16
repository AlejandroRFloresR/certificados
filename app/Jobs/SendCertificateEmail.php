<?php

namespace App\Jobs;

use App\Mail\CertificateIssuedMail;
use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Envía el certificado por correo a su titular.
 * Un error de correo nunca rompe la emisión: queda anotado en el certificado
 * (email_error) y el admin lo puede reenviar desde la ficha del curso.
 */
class SendCertificateEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(public int $certificateId)
    {
    }

    public function handle(): void
    {
        $certificate = Certificate::with('user')->find($this->certificateId);
        if (!$certificate) {
            return;
        }

        $email = $certificate->user?->email;
        if (!$email) {
            $certificate->update(['email_error' => 'El usuario no tiene correo.']);
            return;
        }

        try {
            Mail::to($email, $certificate->user->name)->send(new CertificateIssuedMail($certificate));
            $certificate->update(['emailed_at' => now(), 'email_error' => null]);
        } catch (Throwable $e) {
            Log::warning("No se pudo enviar el certificado {$certificate->id} a {$email}: {$e->getMessage()}");
            $certificate->update(['email_error' => Str::limit($e->getMessage(), 250)]);
        }
    }
}
