<?php

namespace App\Exports;

use App\Models\Course;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CourseUsersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private Course $course) {}

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Email',
            'DNI',
            'Teléfono',
            'Fecha de inscripción',
            'Tipo de certificado',
        ];
    }

    public function collection(): Collection
    {
        // Traemos usuarios del curso + sus roles + su certificado de ESTE curso
        return $this->course->users()
            ->with([
                'roles:id,name',
                'certificates' => function ($q) {
                    $q->where('course_id', $this->course->id)
                      ->select('id','user_id','course_id','type','snapshot_data','created_at');
                }
            ])
            ->withPivot('created_at')
            ->get();
    }

    public function map($user): array
    {
        // Rol actual (si usás 1 rol “principal”):
        $role = $user->roles->pluck('name')->implode(', ');

        // Certificado del curso y su tipo (con fallback a snapshot_data['type'])
        $cert = $user->certificates->first();
        $type = $cert?->type ?? data_get($cert?->snapshot_data, 'type');
        if (is_array($type)) { $type = reset($type) ?: null; }
        $typeLabel = $type ? ucfirst($type) : '';

        return [
            $user->id,
            $user->name,
            $user->email,
            $user->dni,
            $user->telefono,
            optional($user->pivot?->created_at)->format('d/m/Y'),
            $typeLabel,
        ];
    }
}
