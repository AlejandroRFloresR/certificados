<?php

namespace App\Services;

use App\Imports\RawSheetImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class MoodleGradeReader
{
    /** Encabezados posibles (normalizados: minúsculas, sin tildes ni símbolos) */
    private const COLUMNS = [
        'idnumber'  => ['id number', 'idnumber', 'numero de id', 'numero de identificacion'],
        'username'  => ['username', 'nombre de usuario', 'usuario'],
        'firstname' => ['first name', 'firstname', 'nombre', 'primer nombre'],
        'lastname'  => ['last name', 'surname', 'lastname', 'apellido', 'apellidos'],
    ];

    /** El email cambia mucho de nombre entre versiones de Moodle: alcanza con que el encabezado contenga una de estas */
    private const EMAIL_KEYWORDS = ['correo', 'email', 'mail'];

    /** Nombres legibles para explicar qué columnas se reconocieron */
    private const LABELS = [
        'idnumber' => 'Número de ID', 'username' => 'Nombre de usuario', 'email' => 'Email',
        'firstname' => 'Nombre', 'lastname' => 'Apellido', 'total' => 'Total del curso',
    ];

    /** La nota final empieza así; Moodle agrega el formato al final, ej. "Course total (Real)" */
    private const TOTAL_PREFIXES = ['course total', 'total del curso'];

    /**
     * @return array<int, array{row:int, dni:?string, email:?string, name:string, grade:?float}>
     */
    public function read(UploadedFile $file): array
    {
        $sheet = Excel::toArray(new RawSheetImport, $file)[0] ?? [];
        $header = array_map(fn ($h) => $this->normalize($h), array_shift($sheet) ?? []);

        $col = [];
        foreach (self::COLUMNS as $key => $aliases) {
            $index = collect($header)->search(fn ($h) => in_array($h, $aliases, true));
            $col[$key] = $index === false ? null : $index;
        }
        $col['email'] = collect($header)->search(
            fn ($h) => collect(self::EMAIL_KEYWORDS)->contains(fn ($k) => str_contains($h, $k))
        );
        $col['email'] = $col['email'] === false ? null : $col['email'];
        $col['total'] = collect($header)->search(
            fn ($h) => collect(self::TOTAL_PREFIXES)->contains(fn ($p) => str_starts_with($h, $p))
        );
        $col['total'] = $col['total'] === false ? null : $col['total'];

        if ($col['idnumber'] === null && $col['username'] === null && $col['email'] === null) {
            throw ValidationException::withMessages([
                'file' => 'No se encontró una columna para identificar a los alumnos (Número de ID, Nombre de usuario o Email). ¿Es una exportación de calificaciones de Moodle?',
            ]);
        }

        $rows = [];
        foreach ($sheet as $i => $line) {
            $get = fn ($key) => $col[$key] !== null ? trim((string) ($line[$col[$key]] ?? '')) : '';

            // DNI: primero "Número de ID", si no el username (en ambos exportamos el DNI)
            $dni = preg_replace('/\D+/', '', $get('idnumber')) ?: preg_replace('/\D+/', '', $get('username'));
            $email = strtolower($get('email'));

            if ($dni === '' && $email === '') {
                continue; // fila vacía o de totales
            }

            $rows[] = [
                'row'   => $i + 2, // +1 por el encabezado, +1 porque Excel cuenta desde 1
                'dni'   => $dni ?: null,
                'email' => $email ?: null,
                'name'  => trim($get('firstname') . ' ' . $get('lastname')),
                'grade' => $col['total'] !== null ? $this->parseGrade($line[$col['total']] ?? null) : null,
            ];
        }

        // Hay filas con datos pero ninguna trae DNI ni email: explicar en vez de mostrar una tabla vacía
        $dataRows = collect($sheet)->filter(fn ($line) => collect($line)->filter(fn ($v) => trim((string) $v) !== '')->isNotEmpty());
        if ($rows === [] && $dataRows->isNotEmpty()) {
            $found = collect($col)->filter(fn ($i) => $i !== null)->keys()->map(fn ($k) => self::LABELS[$k])->implode(', ');
            throw ValidationException::withMessages([
                'file' => "El archivo tiene {$dataRows->count()} filas, pero ninguna trae DNI (Número de ID / Nombre de usuario) ni email. "
                    . "Columnas reconocidas: " . ($found ?: 'ninguna') . '. Revisá que la exportación de Moodle incluya el email o el Número de ID.',
            ]);
        }

        return $rows;
    }

    public function hasGradeColumn(UploadedFile $file): bool
    {
        $header = Excel::toArray(new RawSheetImport, $file)[0][0] ?? [];
        return collect($header)->contains(
            fn ($h) => collect(self::TOTAL_PREFIXES)->contains(fn ($p) => str_starts_with($this->normalize($h), $p))
        );
    }

    /** "Número de ID" → "numero de id"; "Apellido(s)" → "apellidos" */
    private function normalize($value): string
    {
        $text = Str::lower(Str::ascii((string) $value));
        $text = preg_replace('/[^a-z0-9 ]/', '', $text);
        return trim(preg_replace('/\s+/', ' ', $text));
    }

    /** "85,50" → 85.5; "-" o vacío → null (sin calificar) */
    private function parseGrade($value): ?float
    {
        $text = str_replace(['%', ' '], '', (string) $value);
        $text = str_replace(',', '.', $text);
        return is_numeric($text) ? (float) $text : null;
    }
}
