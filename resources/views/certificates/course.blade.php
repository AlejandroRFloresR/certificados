<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans;
            text-align: center;
            padding: 50px;
        }
        h1 {
            font-size: 28px;
            margin-bottom: 30px;
        }
        .content {
            font-size: 18px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <h1>Certificado de Finalización</h1>

    <div class="content">
        Se certifica que <strong>{{ $user->name }}</strong> ha completado<br>
        el curso <strong>{{ $course->title }}</strong> el día <strong>{{ $date }}</strong>.
    </div>
</body>
</html>