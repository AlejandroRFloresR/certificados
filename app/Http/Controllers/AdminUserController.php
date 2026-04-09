<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    public function __construct()
    {
        // Protegemos todo el panel con rol admin (Spatie)
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $users = User::with('roles')->latest()->paginate(15);
        $roles = Role::all(); // para selects rápidos en index o modal

        return view('admin.users.index', compact('users', 'roles'));
    }

    /** FORMULARIO: crear usuario */
    public function create()
    {
        // traemos roles disponibles para el <select>
        $roles = Role::all(['id','name']);
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','string','lowercase','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8','confirmed'],
            'dni'      => ['required','string','max:20','unique:users,dni'],
            'telefono' => ['required','string','max:20'],
            'role'     => ['required','exists:roles,name'], // <- seleccionás aquí admin/tutor/usuario
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'dni'      => $data['dni'],
            'telefono' => $data['telefono'],
        ]);

        // Asigna el rol seleccionado
        $user->syncRoles([$data['role']]);

        // ⚠️ Si el rol es 'tutor', creamos (o confirmamos) su perfil Tutor
        if ($user->hasRole('tutor')) {
            Tutor::firstOrCreate(
                ['user_id' => $user->id],
                ['name' => $user->name] // la firma se sube después
            );
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    /** FORMULARIO: editar usuario (datos + rol + vínculo tutor) */
    public function edit(User $user)
    {
        $roles       = Role::all();
        $user->load('roles','tutor');

        return view('admin.users.edit', compact('user','roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email,'.$user->id],
            'dni'      => ['required','string','max:20','unique:users,dni,'.$user->id],
            'telefono' => ['required','string','max:20'],
            // role es opcional si se está editando a sí mismo (readonly)
            'role'     => ['nullable','exists:roles,name'],
        ]);

        // actualizar datos básicos
        $user->update([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'dni'      => $data['dni'],
            'telefono' => $data['telefono'],
        ]);

        // --- manejo de rol ---
        if ($user->id !== auth()->id() && isset($data['role'])) {
            // (Opcional) bloquear quitar el último admin
            $currentRole = $user->roles->pluck('name')->first();
            if ($currentRole === 'admin' && $data['role'] !== 'admin') {
                $totalAdmins = \App\Models\User::role('admin')->count();
                if ($totalAdmins <= 1) {
                    return back()->withErrors(['role' => 'No podés quitar el último administrador.'])->withInput();
                }
            }

            $user->syncRoles([$data['role']]);
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado.');
    }

  
    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role'=>'required|exists:roles,name',
        ]);

        $user->syncRoles([$request->role]);

        if ($user->hasRole('tutor')) {
            // crear Tutor si no existe
            Tutor::firstOrCreate(
                ['user_id' => $user->id],
                ['name' => $user->name] // la firma se carga después
            );
        } else {
            // si dejó de ser tutor, eliminar perfil Tutor (cascade limpia pivots)
            if ($user->tutor) {
                $user->tutor()->delete();
            }
        }

        return back()->with('success', 'Rol actualizado correctamente.');
    }
    /** FORM contraseña (lo mantengo tal cual lo tenías) */
    public function editPassword(User $user)
    {
        return view('admin.users.edit-password', compact('user'));
    }

    /** UPDATE contraseña */
    public function updatePassword(Request $request, User $user)
    {
        $request->validate([
            'password' => ['required','confirmed','min:8'],
        ]);

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Contraseña actualizada.');
    }

    /** Eliminar usuario (con seguridad extra y limpieza de vínculo tutor) */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        // “airbag”: por si el hook no corriera (CLI, tareas, etc)
        optional($user->tutor)->delete();

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado.');
    }
}
