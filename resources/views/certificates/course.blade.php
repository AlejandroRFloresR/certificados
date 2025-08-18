<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            background-color: white;
            color: #333;
        }
        .header {
            background-color: #003764;
            color: white;
            padding: 15px;
            margin: 20 30px;
            width: 900px;
            height: 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-align: right;
            border-radius: 10px;
        }
        .header img {
            height: 50px;
        }
        .container {
            padding: 40px;
            text-align: center;
        }
        h1 {
            font-size: 28px;
            margin-bottom: 0;
        }
        .nombre {
            font-size: 32px;
            font-weight: bold;
            margin-top: 15px;
        }
        .detalle {
            font-size: 16px;
            margin-top: 10px;
            line-height: 1.6;
        }
        .footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .firma {
            text-align: center;
        }
        .firma img {
            height: 60px;
        }
        .fecha {
            text-align: center;
            margin-top: 30px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
            <img src="{{ public_path('storage/images/logoCertificado.png') }}" alt="Logo">
    </div>

    <div class="container">
        <h1>Certificado de Finalización</h1>
        <p>El Hospital Universitario de la UNCUYO certifica que</p>
        <div class="nombre">{{ $user->name }},DNI {{$user->dni}}</div> <br>
            ha completado satisfactoriamente el curso <strong>"{{ $course->title }}"</strong> <br>
            dictado por dictado por 
        @foreach($course->tutors as $tutor)
            {{ $tutor->name }}@if(!$loop->last), @endif
        @endforeach
        </p>

        <div class="footer">
            <div class="firma">
               @if($course->tutors->isNotEmpty())
                    <div style="display: table; width: 100%; table-layout: fixed; text-align: center; margin-top: 30px; border-spacing: 90px 0;">
                        <div style="display: table-row;">
                            @foreach($course->tutors as $tutor)
                                @php
                                    $firmaPath = public_path('storage/' . $tutor->signature);
                                @endphp

                                <div style="display: table-cell; vertical-align: top;">
                                    @if(file_exists($firmaPath))
                                        <img src="{{ $firmaPath }}" alt="Firma de {{ $tutor->name }}" style="height: 60px;">
                                    @else
                                        <p style="font-size: 12px; color: gray;">Firma no disponible</p>
                                    @endif
                                    <p style="margin-top: 5px;">{{ $tutor->name }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                            </div>
            <div class="fecha">
                {{ $date }}
            </div>
        </div>
    </div>
</body>
</html>

