<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Certificado</title>
<style>
    @php
    $dir = storage_path('app/fonts/Verdana');
    $R  = 'file://'.str_replace('\\','/',$dir.'/verdana.ttf');
    $B  = 'file://'.str_replace('\\','/',$dir.'/verdanab.ttf');
    $I  = 'file://'.str_replace('\\','/',$dir.'/verdanai.ttf');
    $BI = 'file://'.str_replace('\\','/',$dir.'/verdanaz.ttf');
    @endphp

    @font-face {
        font-family: 'verdana';
        src: url('{{ $R }}') format('truetype');
        font-weight: 400;
        font-style: normal;
    }
    @font-face {
        font-family: 'verdana';
        src: url('{{ $B }}') format('truetype');
        font-weight: 700;
        font-style: normal;
    }
    @font-face {
        font-family: 'verdana';
        src: url('{{ $I }}') format('truetype');
        font-weight: 400;
        font-style: italic;
    }
    @font-face {
        font-family: 'verdana';
        src: url('{{ $BI }}') format('truetype');
        font-weight: 700;
        font-style: italic;
    }
    body { font-family: 'verdana', sans-serif; margin:0; padding:0; background:#fff; color:#333; }

    /* Marca de agua centrada, grande y tenue */
    .watermark {
        position: fixed;
        top: 50%;
        left: 45%;
        transform: translate(-50%, -50%) scale(1.2);
        opacity: 1;
        width: 45%;
        height: auto;
        z-index: 1;
    }

    .page { position: relative; z-index: 2; }

    .header {
    background-color: #003764;
    color: #fff;
    padding: 5px 20px;
    border-radius: 1%;
    position: relative;
    min-height: 56px;
    margin: 35px 60px;
    }

    /* ✅ forzar extremos con floats */
    .header img.qr {
        float: left;
        height: 70px;
        display: block;
    }

    .header img.logo {
        float: right;
        height: 70px;
        display: block;
    }

    /* ✅ clearfix para que el header envuelva las imágenes flotadas */
    .header::after {
        content: "";
        display: block;
        clear: both;
    }
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
    @if(!empty($watermark_data))
        <img class="watermark" src="{{ $watermark_data }}" alt="Marca de agua">
    @endif

    <div class="page">
        <div class="header">
            <img class="qr" src="{{ $qr_data_uri }}" alt="QR de verificación">
                @if(!empty($logo_data))
                    <img class="logo" src="{{ $logo_data }}" alt="Logo">
                @endif
                
        </div>

        
        @php
            // Tipo desde snapshot (fallback a 'aprobado' si faltara)
            $type = strtolower($snap['type'] ?? 'aprobado');

            // Título y texto dinámicos según tipo
            switch ($type) {
                case 'asistio':
                    $titleText = 'Certificado de Asistencia';
                    // “asistió” con tilde
                    $bodyText  = 'asistió al curso';
                    break;
                case 'dicto':
                    $titleText = 'Certificado de Docencia';
                    // “dictó” con tilde
                    $bodyText  = 'dictó el curso';
                    break;
                case 'aprobado':
                default:
                    $titleText = 'Certificado de Aprobación';
                    $bodyText  = 'aprobó el curso';
                    break;
            }
        @endphp

        <div class="container">
            <h1>{{ $titleText }} </h1>
            <p>El Hospital Universitario de la UNCUYO certifica que</p>

            <div class="nombre">
                {{ mb_strtoupper($snap['student']['name']) }}
                @if(!empty($snap['student']['dni'])), DNI {{ $snap['student']['dni'] }}@endif
            </div>

            <p class="detalle">
                {{ $bodyText }}
                <strong>{{ $snap['course']['title'] }}</strong>.
            </p>

             <div class="fecha">Mendoza, {{ $date_long }}</div>
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

        </div>
    </div>
</body>
</html>
