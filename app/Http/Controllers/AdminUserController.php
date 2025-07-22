<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Models\Course;


class AdminUserController extends Controller
{
    
    public function index()
    {
        $users=User::with('roles')->get();
        $roles=Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role'=>'required|exists:roles,name',
        ]);
        $user->syncRoles([$request->role]);
        return redirect()->back()->with('success', 'Rol actualizado correctamente.');
    }

}
