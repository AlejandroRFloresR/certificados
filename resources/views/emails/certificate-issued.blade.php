<x-mail::message>
# ¡Hola, {{ $name }}!

@if ($taught)
Gracias por dictar el curso **{{ $courseTitle }}**. Te adjuntamos tu certificado en PDF.
@else
Finalizaste el curso **{{ $courseTitle }}**. Te adjuntamos tu certificado en PDF.
@endif

También podés descargarlo cuando quieras desde este botón:

<x-mail::button :url="$downloadUrl">
Descargar certificado
</x-mail::button>

@if ($surveyUrl)
## ¿Nos ayudás a mejorar?

Contanos qué te pareció el curso. Son 5 preguntas y lleva menos de 2 minutos.

<x-mail::button :url="$surveyUrl" color="success">
Responder la encuesta
</x-mail::button>
@endif

<x-mail::subcopy>
Cualquier persona puede verificar la validez del certificado con el código **{{ $code }}** en [{{ $verifyUrl }}]({{ $verifyUrl }}) o escaneando el QR del PDF.
</x-mail::subcopy>
</x-mail::message>
