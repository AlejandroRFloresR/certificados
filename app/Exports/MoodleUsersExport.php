<?php

namespace App\Exports;

use App\Models\Course;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * CSV para Moodle → Administración del sitio → Usuarios → Subir usuarios.
 * username e idnumber = DNI, para poder cruzar después las calificaciones.
 */
class MoodleUsersExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private Course $course) {}

    public function headings(): array
    {
        $headings = ['username', 'firstname', 'lastname', 'email', 'idnumber', 'phone1'];

        // Solo si el curso tiene nombre corto: Moodle inscribe con course1 + role1
        if ($this->course->moodle_shortname) {
            array_push($headings, 'course1', 'role1');
        }

        return $headings;
    }

    public function collection(): Collection
    {
        // Moodle exige username y email: se exportan solo los alumnos que tienen ambos
        return $this->course->users()
            ->whereNotNull('dni')->where('dni', '!=', '')
            ->whereNotNull('email')->where('email', '!=', '')
            ->orderBy('name')
            ->get(['users.id', 'name', 'email', 'dni', 'telefono']);
    }

    public function map($user): array
    {
        $dni = preg_replace('/\D+/', '', (string) $user->dni);
        [$firstname, $lastname] = $this->splitName($user->name);

        $row = [$dni, $firstname, $lastname, strtolower($user->email), $dni, (string) $user->telefono];

        if ($this->course->moodle_shortname) {
            array_push($row, $this->course->moodle_shortname, 'student');
        }

        return $row;
    }

    /** "Juan Carlos Pérez" → ["Juan Carlos", "Pérez"]. Moodle no acepta apellido vacío. */
    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name));

        if (count($parts) < 2) {
            return [$parts[0] ?? '-', '-'];
        }

        $lastname = array_pop($parts);
        return [implode(' ', $parts), $lastname];
    }
}
