<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class UsersImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    private int $created = 0;
    private int $updated = 0;
    private ?string $defaultRole;
    private ?int $enrollCourseId;

    /**
     * @param string|null $defaultRole   Rol por defecto si la fila no trae 'role'
     * @param int|null    $enrollCourseId  Curso al que se inscribe a todos (opcional)
     */
    public function __construct(?string $defaultRole = null, ?int $enrollCourseId = null)
    {
        $this->defaultRole   = $defaultRole;
        $this->enrollCourseId = $enrollCourseId;
    }

    public function rules(): array
    {
        // Las cabeceras deben coincidir con el archivo (ver sección 5)
        return [
            '*.nombre'     => ['required','string','max:255'],
            '*.apellido'     => ['required','string','max:255'],
            '*.correo_electronico'    => ['required','email','max:255'],
            '*.dni'      => ['required','regex:/^\d{6,12}$/'],
            '*.telefono' => ['nullable','regex:/^[0-9+\s\-().]{6,12}$/'],
            '*.role'     => ['nullable', Rule::in(['admin','tutor','user'])],
            // opcional: course_ids como "1|3|5"
            '*.course_ids' => ['nullable','string'],
            '*.password' => ['nullable','string','min:8'], // si no viene, generamos una
        ];
    }

    public function model(array $row)
    {
        $nombre   = (string)($row['nombre']    ?? $row['nombre_'] ?? '');
        $apellido = (string)($row['apellido']  ?? '');
        $email    = (string)($row['correo_electronico'] ?? $row['correo'] ?? '');

        // Nombre completo para el campo name
        $name = trim(preg_replace('/\s+/', ' ', $nombre . ' ' . $apellido));

        // Normalizar DNI a solo dígitos
        $dniRaw = $row['dni'] ?? '';
        $dni    = preg_replace('/\D+/', '', (string)$dniRaw);

        // Si más adelante agregás teléfono en la planilla:
        $telefonoRaw = $row['telefono'] ?? null;
        $telefono = $telefonoRaw !== null
            ? preg_replace('/[^\d+\s\-().]/', '', (string)$telefonoRaw)
            : null;

        $role = $row['role'] ?? $this->defaultRole ?? 'user';

        // Upsert por email
        $user = User::where('email', $email)->first();

        $data = [
            'name'     => $name,
            'email'    => $email,
            'dni'      => $dni,
            'telefono' => $telefono, // en tu planilla actual no viene, quedará null
        ];

        if (!$user) {
            $data['password'] = Hash::make($row['password'] ?? $dni);
            $user = User::create($data);
            $this->created++;
        } else {
                // si querés evitar updates, comentá esto
            //$user->fill($data)->save();
            //$this->updated++;
        }

        // Asignar rol (Spatie)
        $user->syncRoles([$role]);

        // Inscribir a cursos si viene en columna course_ids "1|3|5"
        if (!empty($row['course_ids'])) {
            $ids = collect(explode('|', $row['course_ids']))
                ->filter(fn($v) => is_numeric($v))
                ->map(fn($v) => (int) $v)
                ->values()
                ->all();
            if ($ids) {
                $user->courses()->syncWithoutDetaching($ids);
            }
        }

        // Inscribir al curso masivo pasado por formulario (opcional)
        if (!empty($this->enrollCourseId)) {
            $user->courses()->syncWithoutDetaching([$this->enrollCourseId]);
        }

        return $user;
    }

    // Contadores & fallas
    public function createdCount(): int { return $this->created; }
    public function updatedCount(): int { return $this->updated; }
}
