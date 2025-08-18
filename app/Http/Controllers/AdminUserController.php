<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
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

    public function editPassword(User $user)
    {
        return view('admin.users.edit-password', compact('user'));
    }

    public function updatePassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|confirmed|min:8',
            ]);

        $user->password = Hash::make($request->password);
        $user->save();

       return redirect()->route('admin.users.index')->with('success', 'Contraseña actualizada.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado.');
    }
}
