<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;

class AdminUserImportController extends Controller
{
    public function create()
    {
        return view('admin.users.import'); // form de subida
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required','file','mimes:xlsx,xls,csv','max:10240'], // 10MB
            'default_role' => ['nullable','in:admin,tutor,user'],
            'enroll_course_id' => ['nullable','exists:courses,id'],
        ]);

        $defaultRole = $request->input('default_role');       // rol por defecto si no viene en archivo
        $courseId     = $request->input('enroll_course_id');  // inscribir a un curso opcional

        $import = new UsersImport($defaultRole, $courseId);
        Excel::import($import, $request->file('file'));

        $created = $import->createdCount();
        $updated = $import->updatedCount();
        $skipped = $import->failures()->count();

        return back()->with('success', "Importación finalizada. Creados: $created · Actualizados: $updated · Fallidos: $skipped")
                     ->with('failures', $import->failures());
    }
}
