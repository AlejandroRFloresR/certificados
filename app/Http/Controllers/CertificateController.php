<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificateController extends Controller
{
    public function generate(Course $course, User $user)
    {
            if(!$user->courses->contains($course->id))
        {
            abort(403, 'No estas inscrito en este curso');
        }
        
         // Cargar los tutores asociados al curso
        $course->load('tutors');
        $data=[
            'user'=>$user,
            'course'=>$course,
            'date'=> now()->format('d/m/Y'),
        ];
        
        $pdf =Pdf::loadview('certificates.course', $data)->setPaper('A4', 'landscape');

        return $pdf->download("Certificado_{$user->name}_{$course->title}.pdf");
    }

}
