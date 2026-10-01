<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class AdminUserImportController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Admin/Users/Import', [
            'courses' => Course::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file'             => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'], // 10MB
            'default_role'     => ['nullable', 'in:admin,tutor,user'],
            'enroll_course_id' => ['nullable', 'exists:courses,id'],
        ]);

        $import = new UsersImport($request->input('default_role'), $request->input('enroll_course_id'));
        Excel::import($import, $request->file('file'));

        $created = $import->createdCount();
        $updated = $import->updatedCount();
        $skipped = $import->failures()->count();

        // Solo fila + mensajes: no guardamos en sesión los datos de cada fila
        $failures = $import->failures()
            ->map(fn ($f) => ['row' => $f->row(), 'errors' => $f->errors()])
            ->values()
            ->all();

        return back()
            ->with('success', "Importación finalizada. Creados: $created · Actualizados: $updated · Fallidos: $skipped")
            ->with('failures', $failures);
    }
}
