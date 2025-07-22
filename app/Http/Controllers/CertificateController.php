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
    public function generate(Course $course)
    {
        $user=auth()->user();
        if(!$user->courses->contains($course->id))
        {
            abort(403, 'No estas inscrito en este curso');
        }
        $pdf =Pdf::loadview('certificates.course',[
            'user'=>$user,
            'course'=>$course,
            'date'=> now()->format('d/m/Y'),
        ]);

        return $pdf->download("Certificado_{$user->name}_{$course->title}.pdf");
    }

    public function issue(Request $request)
    {

        $request->validate([
            'user_id'=> 'required|exists:users,id',
            'course_id'=> 'required|exists:courses,id'
        ]);

        $exists = Certificate::where('user_id', $request->user_id)->where('course_id', $request->course_id)->first();
        
        if($exists){
            return back()->with('error', 'Este usuario ya tiene un certificado para este curso');
        }

        Certificate::create([
            'user_id'=> $request->user_id,
            'course_id'=> $request->course_id,
            'issued_date' => now(),
            'certificate_code' => strtoupper(Str::random(10)),
        ]);

        return back()->with('sucess', 'Certificado emitido con exito');
    }


}
