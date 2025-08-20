<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Certificado</title>
<style>
    body { font-family: 'DejaVu Sans', sans-serif; margin:0; padding:0; background:#fff; color:#333; }

    /* Marca de agua centrada, grande y tenue */
    .watermark {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(1.2);
        opacity: 1;
        width: 75%;
        height: auto;
        z-index: 1;
    }

    .page { position: relative; z-index: 2; }

    .header {
        background-color: #003764;
        color: #fff;
        padding: 15px 20px;
        display:flex;
        justify-content:space-between;
        align-items:center;
    }

    .brand {
        display:flex;
        align-items:center;
        gap: 12px;          /* separa logo y QR */
    }

    .brand img.logo { height: 46px; display:block; }
    .brand img.qr   { height: 46px; display:block; } /* mismo alto que el logo */
    .header-title { font-size: 20px; margin: 0; }
    .container { padding: 40px; text-align: center; position: relative; }
    h1 { font-size: 28px; margin-bottom: 0; }
    .nombre { font-size: 32px; font-weight: bold; margin-top: 15px; }
    .detalle { font-size: 16px; margin-top: 10px; line-height: 1.6; }
    .firmas { display: table; width: 100%; table-layout: fixed; text-align: center; margin-top: 30px; border-spacing: 20px 0; }
    .firmas-row { display: table-row; }
    .firma-cell { display: table-cell; vertical-align: top; padding: 0 30px; }
    .firma-cell img { height: 60px; display:block; margin:0 auto; }
    .fecha { text-align:center; margin-top:30px; font-size:14px; }
    .codigo { text-align:center; margin-top:10px; font-size:12px; color:#666; }
</style>
</head>
<body>

    {{-- Marca de agua (opcional) --}}
    @if(!empty($watermark_data))
        <img class="watermark" src="{{ $watermark_data }}" alt="Marca de agua">
    @endif

    <div class="page">
        <div class="header">
            <h2 class="header-title">Hospital Universitario</h2>

            <div class="brand">
                {{-- LOGO (data-URI para máxima compatibilidad) --}}
                @if(!empty($logo_data))
                    <img class="logo" src="{{ $logo_data }}" alt="Logo">
                @endif

                {{-- QR BLANCO sobre azul, al lado del logo --}}
                <img class="qr" src="{{ $qr_data_uri }}" alt="QR de verificación">
            </div>
        </div>
        {{-- DEBUG: largo del data-URI del logo --}}
        {{ isset($logo_data) ? strlen($logo_data) : 0 }}

        <div class="container">
            <h1>Certificado de Finalización</h1>
            <p>El Hospital Universitario de la UNCUYO certifica que</p>

            <div class="nombre">
                {{ $snap['student']['name'] }}
                @if(!empty($snap['student']['dni'])), DNI {{ $snap['student']['dni'] }}@endif
            </div>

            <p class="detalle">
                ha completado satisfactoriamente el curso <strong>{{ $snap['course']['title'] }}</strong>.
            </p>

            @if(!empty($tutors))
                <div class="firmas">
                    <div class="firmas-row">
                        @foreach($tutors as $t)
                            <div class="firma-cell">
                                @if(!empty($t['signature_data_uri']))
                                    <img src="{{ $t['signature_data_uri'] }}" alt="Firma de {{ $t['name'] }}">
                                @else
                                    <div style="height:60px;"></div>
                                @endif
                                <p style="margin-top:5px;">{{ $t['name'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="fecha">{{ $date }}</div>
            <div class="codigo">Código de verificación: {{ $code }}</div>
        </div>
    </div>
</body>
</html>
